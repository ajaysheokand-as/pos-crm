<script>


$(document).ready(function() {
 $(function () {
    $('.update_ekyc_link').off('click').on('click', function() {
        // Get the lead ID
        var lead_id = $('#lead_id').val();

        if (!lead_id) {
            catchError("Lead ID cannot be empty");
            return false;
        }

        // AJAX request
        $.ajax({
            url: '<?= base_url("SupportController/update_lead/") ?>',
            type: 'POST',
            dataType: "json",
            data: {lead_id: lead_id, csrf_token},
            beforeSend: function () {
                $('#update_ekyc_link').html('<span class="spinner-border spinner-border-sm" role="status"></span>Processing...').addClass('disabled');
            },
            success: function (data) {
                // Reset button state
                
                $('#update_ekyc_link').html('Reset').removeClass('disabled');
                
                if (data.err) {
                    catchError(data.err);
                } else {
                    // Success message and additional actions
                    catchSuccess(data.msg);
                   // alert("Lead ID: " + lead_id);  // Debugging alert
                }
            },
            error: function(xhr) {
                console.error(xhr.responseText);  // Log error for debugging
                catchError('An error occurred while updating the lead.');
                $('#update_ekyc_link').html('Reset').removeClass('disabled');  // Reset button state
            }
        });
    });
});
});


// esign start here
$(document).ready(function() {
 $(function () {
    $('.update_esign_link').off('click').on('click', function() {
        // Get the lead ID
        var lead_id = $('#lead_id').val();

        // CSRF token variables
        var csrfName = '<?php echo $this->security->get_csrf_token_name(); ?>';
        var csrfHash = '<?php echo $this->security->get_csrf_hash(); ?>';

        // Input validation
        if (!lead_id) {
            catchError("Lead ID cannot be empty");
            return false;
        }

        // AJAX request
        $.ajax({
            url: '<?= base_url("SupportController/update_esign/") ?>',
            type: 'POST',
            dataType: "json",
            data: {
                lead_id: lead_id,
                [csrfName]: csrfHash  // Include CSRF token
            },
            beforeSend: function () {
                $('#update_esign_link').html('<span class="spinner-border spinner-border-sm" role="status"></span>Processing...').addClass('disabled');
            },
            success: function (data) {
                // Reset button state
                $('#update_esign_link').html('Reset').removeClass('disabled');
                
                if (data.err) {
                    catchError(data.err);
                } else {
                    // Success message and additional actions
                    catchSuccess(data.msg);
                   // alert("Lead ID: " + lead_id);  // Debugging alert
                }
            },
            error: function(xhr) {
                console.error(xhr.responseText);  // Log error for debugging
                catchError('An error occurred while updating the lead.');
                $('#update_ekyc_link').html('Reset').removeClass('disabled');  // Reset button state
            }
        });
    });
});
});



// add pin code here





$(document).ready(function() {
    $('#add_link').click(function(event) {
        event.preventDefault(); // Prevent default form submission
        
         if (!$('#m_pincode_value')[0].checkValidity()) {
            alert('Pincode is required and must be numeric.');
            return;
        }

        var formData = $('#formData').serialize();
        
        
        $('#loadingIndicator').show(); 

        $.ajax({
            type: 'POST',
            url: '<?= base_url('/SupportControllerekyc/add_pincode/') ?>', // Adjust to your controller method
            data: formData,
            dataType: 'json', 
            success: function(response) {
                
                if (response.msg) {
                    alert(response.msg); 
                    $('#formData')[0].reset(); 
                } else {
                    // Display error message
                    alert(response.err || 'An unknown error occurred.');
                }
            },
            error: function(xhr, status, error) {
                // Handle error response
                alert('An error occurred: ' + error);
            },
            complete: function() {
                // Hide loading indicator
                $('#loadingIndicator').hide(); 
            }
        });
    });
});








</script>
