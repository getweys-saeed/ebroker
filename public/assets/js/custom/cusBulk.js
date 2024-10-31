const selectAllCheckbox = $('#selectAllCheck');

// Event listener for the Select All checkbox
selectAllCheckbox.on('click', function() {
    const isChecked = this.checked;
    console.log("Select All clicked. Checked:", isChecked); // Debugging log

    // Check/uncheck all checkboxes
    $('input[name="checkId"]').prop('checked', isChecked);
    console.log(isChecked ? "All rows checked." : "All rows unchecked."); // Debugging log
});

// Update Select All checkbox based on individual checkbox changes
$('input[name="checkId"]').on('change', function() {
    const totalCheckboxes = $('input[name="checkId"]').length;
    const checkedCheckboxes = $('input[name="checkId"]:checked').length;
    selectAllCheckbox.prop('checked', totalCheckboxes === checkedCheckboxes);
    console.log("Checkboxes checked:", checkedCheckboxes, "of", totalCheckboxes); // Debugging log
});




$('#applyBulkAction').click(function() {
    var action = $('#bulkAction').val();
    var selectedIds = [];

    // Collect selected IDs
    $('input[name="checkId"]:checked').each(function() {
        selectedIds.push($(this).val());
    });

    if ((action === 'delete' || action === 'activate' || action === 'deactivate') && selectedIds.length >
        0) {
        // Use SweetAlert2 for confirmation
        Swal.fire({
            title: 'Are you sure?',
            text: 'This action cannot be undone!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, proceed!'
        }).then((result) => {
            if (result.isConfirmed) {
                // Send AJAX request to perform bulk action
                $.ajax({
                    url: "{{ route('customer.BulkUpdate') }}", // Update this route in your web.php
                    type: 'POST',
                    data: {
                        ids: selectedIds,
                        action: action,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            Toastify({
                                text: response.message,
                                duration: 3000,
                                gravity: "top", // `top` or `bottom`
                                position: "right", // `left`, `center` or `right`
                                backgroundColor: "linear-gradient(to right, #00b09b, #96c93d)",
                            }).showToast();

                            $('#table_list').bootstrapTable('refresh');
                        } else {
                            Toastify({
                                text: "Failed to Update",
                                duration: 3000,
                                gravity: "top",
                                position: "right",
                                backgroundColor: "linear-gradient(to right, #00b09b, #96c93d)",
                            }).showToast();
                        }
                    },
                    error: function(xhr) {
                        console.error('An error occurred:', xhr);

                        Toastify({
                            text: "An error occurred while performing the bulk action.",
                            duration: 3000,
                            gravity: "top",
                            position: "right",
                            backgroundColor: "linear-gradient(to right, #00b09b, #96c93d)",
                        }).showToast();
                    }
                });
            }
        });
    } else if (selectedIds.length === 0) {
        // Use SweetAlert2 for alert
        Swal.fire({
            icon: 'info',
            title: 'No Records Selected',
            text: 'Please select at least one record.'
        });
    }
});


function bulkAction(value, row, index) {
    return ` <input type="checkbox" class="form-check-input" name="checkId" value="${row.id}">`;
}
