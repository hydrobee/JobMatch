<?php

namespace App\Services\Jobs;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use SimpleXMLElement;
use Throwable;

class RssJobFetcher
{
    private const DC_NS = 'http://purl.org/dc/elements/1.1/';

    /**
     * @return array<int, array<string, mixed>>
     */
    public function fetch(string $url): array
    {
        $body = Http::timeout(20)
            ->withUserAgent('JobMatch/1.0')
            ->get($url)
            ->throw()
            ->body();

        return $this->parse($body);
    }

    /**
     * Parse RSS 2.0 or Atom into plain job arrays.
     *
     * @return array<int, array<string, mixed>>
     */
    public function parse(string $xml): array
    {
        $previous = libxml_use_internal_errors(true);
        $doc = simplexml_load_string($xml, SimpleXMLElement::class, LIBXML_NOCDATA | LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if ($doc === false) {
            throw new RuntimeException('The feed is not valid XML.');
        }

        $jobs = [];

        if (isset($doc->channel->item)) {
            foreach ($doc->channel->item as $item) {
                $jobs[] = $this->fromRssItem($item);
            }
        } elseif (isset($doc->entry)) {
            foreach ($doc->entry as $entry) {
                $jobs[] = $this->fromAtomEntry($entry);
            }
        } else {
            throw new RuntimeException('No jobs found: the feed is neither RSS nor Atom.');
        }

        return array_values(array_filter($jobs));
    }

    /**
     * @return array<string, mixed>|null
     */
    private function fromRssItem(SimpleXMLElement $item): ?array
    {
        $title = trim((string) $item->title);

        if ($title === '') {
            return null;
        }

        $dc = $item->children(self::DC_NS);
        $company = trim((string) ($item->author ?: $dc->creator));

        [$title, $titleCompany] = $this->splitTitle($title);

        return [
            'title' => $title,
            'company' => $company !== '' ? $company : $titleCompany,
            'location' => trim((string) $item->location) ?: null,
            'description' => $this->clean((string) $item->description),
            'url' => trim((string) $item->link) ?: null,
            'posted_at' => $this->date((string) $item->pubDate),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function fromAtomEntry(SimpleXMLElement $entry): ?array
    {
        $title = trim((string) $entry->title);

        if ($title === '') {
            return null;
        }

        [$title, $titleCompany] = $this->splitTitle($title);
        $company = trim((string) $entry->author->name);

        return [
            'title' => $title,
            'company' => $company !== '' ? $company : $titleCompany,
            'location' => null,
            'description' => $this->clean((string) ($entry->content ?: $entry->summary)),
            'url' => trim((string) $entry->link['href']) ?: null,
            'posted_at' => $this->date((string) ($entry->updated ?: $entry->published)),
        ];
    }

    /**
     * "Software Developer at Acme" => ["Software Developer", "Acme"]
     *
     * @return array{0: string, 1: string|null}
     */
    private function splitTitle(string $title): array
    {
        if (preg_match('/^(.+?)\s+(?:at|@)\s+(.+)$/iu', $title, $m) === 1) {
            return [trim($m[1]), trim($m[2])];
        }

        return [$title, null];
    }

    private function clean(string $html): string
    {
        return trim(strip_tags(html_entity_decode($html, ENT_QUOTES | ENT_HTML5)));
    }

    private function date(string $value): ?Carbon
    {
        if (trim($value) === '') {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (Throwable) {
            return null;
        }
    }
}
