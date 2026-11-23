<x-app-layout width="narrow">
    <x-slot name="header">
        <div class="page-header">
            <div>
                <h1 class="page-title">{{ __('Add a job') }}</h1>
                <p class="page-description">Paste a posting by hand. Skills are detected from the description and scored against yours.</p>
            </div>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('jobs.store') }}" class="card">
        @csrf

        @include('jobs.partials.form-fields', ['listing' => null])

        <div class="card-footer">
            <a href="{{ route('jobs.index') }}" class="btn-secondary">Cancel</a>
            <x-primary-button>Save job</x-primary-button>
        </div>
    </form>
</x-app-layout>
