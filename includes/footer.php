        </div>
        <!-- End Page Content -->
        
        <!-- Footer -->
        <footer class="footer">
            <div class="footer-content">
                <div class="footer-left">
                    <p>&copy; <?= date('Y') ?> Bachat Gat Smart Management System. All rights reserved.</p>
                </div>
                <div class="footer-right">
                    <a href="<?= BASE_URL ?>pages/privacy-policy.php">Privacy Policy</a>
                    <span class="separator">|</span>
                    <a href="<?= BASE_URL ?>pages/terms.php">Terms of Service</a>
                    <span class="separator">|</span>
                    <a href="<?= BASE_URL ?>pages/help.php">Help</a>
                </div>
            </div>
        </footer>
    </div>
    <!-- End Main Content -->
    
    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- DataTables JS (if included) -->
    <?php if (isset($includeDataTables) && $includeDataTables): ?>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.0/dist/jquery.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <?php endif; ?>
    
    <!-- Custom JavaScript -->
    <script>
        // Mobile Sidebar Toggle
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('active');
        }
        
        // Auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
            alerts.forEach(alert => {
                setTimeout(() => {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });
        });
        
        // Confirm before delete
        function confirmDelete(message) {
            return confirm(message || 'Are you sure you want to delete this item? This action cannot be undone.');
        }
        
        // Format currency to Indian format
        function formatCurrency(amount) {
            return '₹' + parseFloat(amount).toLocaleString('en-IN', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }
        
        // Display loading spinner on buttons
        function showLoading(button, text = 'Processing...') {
            const originalText = button.innerHTML;
            button.setAttribute('data-original-text', originalText);
            button.disabled = true;
            button.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>${text}`;
        }
        
        // Hide loading spinner on buttons
        function hideLoading(button) {
            const originalText = button.getAttribute('data-original-text');
            button.disabled = false;
            button.innerHTML = originalText;
        }
        
        // Copy to clipboard
        function copyToClipboard(text, successMessage = 'Copied to clipboard!') {
            navigator.clipboard.writeText(text).then(() => {
                alert(successMessage);
            }).catch(err => {
                console.error('Failed to copy:', err);
            });
        }
        
        // Initialize tooltips
        document.addEventListener('DOMContentLoaded', function() {
            const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
        });
    </script>
    
    <?php if (isset($customJS)): ?>
        <?= $customJS ?>
    <?php endif; ?>
    
    <style>
        /* Footer Styles */
        .footer {
            background: white;
            border-top: 1px solid #e5e7eb;
            padding: 1.5rem 2rem;
            margin-top: 3rem;
        }
        
        .footer-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .footer-left p {
            margin: 0;
            color: #6b7280;
            font-size: 0.9rem;
        }
        
        .footer-right {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .footer-right a {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s ease;
        }
        
        .footer-right a:hover {
            color: var(--secondary-color);
        }
        
        .footer-right .separator {
            color: #d1d5db;
        }
        
        @media (max-width: 768px) {
            .footer {
                padding: 1rem;
            }
            
            .footer-content {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</body>
</html>
