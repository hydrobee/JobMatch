<x-app-layout width="narrow">
    <x-slot name="header">
        <div class="page-header">
            <div>
                <h1 class="page-title">{{ __('Edit job') }}</h1>
                <p class="page-description">Changes to the title or description re-detect skills and update the match score.</p>
            </div>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('jobs.update', $listing) }}" class="card">
        @csrf
        @method('PUT')

        @include('jobs.partials.form-fields', ['listing' => $listing])

        <div class="card-footer">
            <a href="{{ route('jobs.show', $listing) }}" class="btn-secondary">Cancel</a>
            <x-primary-button>Save changes</x-primary-button>
        </div>
    </form>
</x-app-layout>
