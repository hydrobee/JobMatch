        <div class="card-body space-y-5">
            <div>
                <x-input-label for="title" value="Job title" />
                <x-text-input id="title" name="title" class="mt-1.5 w-full" :value="old('title', $listing?->title)" required />
                <x-input-error :messages="$errors->get('title')" class="mt-2" />
            </div>

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <div>
                    <x-input-label for="company" value="Company" />
                    <x-text-input id="company" name="company" class="mt-1.5 w-full" :value="old('company', $listing?->company)" required />
                    <x-input-error :messages="$errors->get('company')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="location" value="Location" />
                    <x-text-input id="location" name="location" class="mt-1.5 w-full" :value="old('location', $listing?->location)" />
                    <x-input-error :messages="$errors->get('location')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="url" value="Link to the posting" />
                    <x-text-input id="url" name="url" type="url" class="mt-1.5 w-full" :value="old('url', $listing?->url)" placeholder="https://" />
                    <x-input-error :messages="$errors->get('url')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="salary" value="Salary (optional)" />
                    <x-text-input id="salary" name="salary" class="mt-1.5 w-full" :value="old('salary', $listing?->salary)" />
                </div>
                <div>
                    <x-input-label for="posted_at" value="Date posted (optional)" />
                    <x-text-input id="posted_at" name="posted_at" type="date" class="mt-1.5 w-full" :value="old('posted_at', $listing?->posted_at?->format('Y-m-d'))" />
                    <x-input-error :messages="$errors->get('posted_at')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="closes_at" value="Closing date (optional)" />
                    <x-text-input id="closes_at" name="closes_at" type="date" class="mt-1.5 w-full" :value="old('closes_at', $listing?->closes_at?->format('Y-m-d'))" />
                    <x-input-error :messages="$errors->get('closes_at')" class="mt-2" />
                </div>
            </div>

            <div>
                <x-input-label for="description" value="Description" />
                <textarea id="description" name="description" rows="10" class="textarea mt-1.5">{{ old('description', $listing?->description) }}</textarea>
                <p class="hint">Paste the full posting; skills are detected from this text.</p>
                <x-input-error :messages="$errors->get('description')" class="mt-2" />
            </div>
        </div>
