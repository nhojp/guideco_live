<!-- Toast Notification -->
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 9999;">
    <?php if (isset($toast_message)): ?>
        <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <strong class="me-auto"><?php echo ucfirst($toast_class); ?></strong>
                <small class="text-muted">Just now</small>
                <button type="button" class="btn btn-outline-danger ml-2" data-bs-dismiss="toast" aria-label="Close">&times;</button>
            </div>
            <div class="toast-body text-<?php echo $toast_class; ?>">
                <?php echo $toast_message; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Include Bootstrap JS and Popper.js -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>

<script>
    // Automatically fade out the toast after 5 seconds (5000ms)
    setTimeout(function() {
        const toastElement = document.querySelector('.toast');
        if (toastElement) {
            const toast = new bootstrap.Toast(toastElement);
            toast.hide();
        }
    }, 5000); // 5000ms = 5 seconds
</script>
