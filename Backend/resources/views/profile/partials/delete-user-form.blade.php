<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Delete Account') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
        </p>
    </header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >{{ __('Delete Account') }}</x-danger-button>

    <x-confirm-action-modal
        name="confirm-user-deletion"
        action="{{ route('profile.destroy') }}"
        method="DELETE"
        title="Confirm Account Deletion"
        message="Once your account is deleted, all of its resources and data will be permanently deleted. Enter your password to confirm."
        confirmText="Delete Account"
        icon="fa-trash"
        passwordRequired
        passwordLabel="Password"
        :password-error-messages="$errors->userDeletion->get('password')"
        :show="$errors->userDeletion->isNotEmpty()"
    />
</section>
