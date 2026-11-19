@props(['name', 'action', 'itemName' => 'this record', 'method' => 'DELETE'])

<x-confirm-action-modal
    :name="$name"
    :action="$action"
    :method="$method"
    title="Confirm Deletion"
    message=""
    confirmText="Delete Permanently"
    icon="fa-trash"
    passwordRequired
    passwordLabel="Enter your password to confirm"
>
    <p class="text-base leading-6 text-gray-600">Are you sure you want to permanently delete <strong>{{ $itemName }}</strong>? This action cannot be undone.</p>
</x-confirm-action-modal>
