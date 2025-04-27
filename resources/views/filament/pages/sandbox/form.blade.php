<x-filament::page>
<!-- Custom header section -->
    <div class="mb-8 bg-white rounded-lg shadow p-6">
        <h2 class="text-2xl font-bold text-gray-900">Custom Form Header</h2>
        <p class="mt-2 text-gray-600">This is a custom section before the form</p>
    </div>

    <!-- Split layout with sidebar -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main form area -->
        <div class="lg:col-span-2">
    <form wire:submit.prevent="save">
<!-- Your Filament form -->
        {{ $this->form }}

<div class="mt-6">
        <x-filament::button type="submit">
            Submit Form
        </x-filament::button>
</div>
    </form>
</div>

        <!-- Sidebar content -->
        <div class="space-y-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900">Form Guidelines</h3>
                <div class="mt-4 text-sm text-gray-600">
                    <ul class="list-disc list-inside space-y-2">
                        <li>Fill in all required fields marked with *</li>
                        <li>Your email will be used for notifications</li>
                        <li>Choose your interests carefully</li>
                    </ul>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900">Need Help?</h3>
                <p class="mt-2 text-sm text-gray-600">
                    Contact support at support@example.com
                </p>
            </div>
        </div>
    </div>

    <!-- Custom footer section -->
    <div class="mt-8 bg-gray-50 rounded-lg p-6">
        <p class="text-sm text-gray-600 text-center">
            By submitting this form, you agree to our terms and conditions.
        </p>
    </div>

    <script>
        function searchName(keyword) {
            console.log('You are typing: ', keyword);
            // Add your AJAX logic here
        }
    </script>
</x-filament::page>