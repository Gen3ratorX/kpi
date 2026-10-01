<?php
require_once '../misc/employee_login_required.php';
$tasksHtml = $taskControl->generateEmployeeTasksHtml($employeeId, $projectId);
?>

<!-- ===== DESIGN IMPROVEMENTS ===== -->
<style>
    /* ===== MODERN TASKS SECTION STYLES ===== */
    
    .tasks-wrapper {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0.5rem 0;
    }

    /* Tasks Header */
    .tasks-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid rgba(16, 185, 129, 0.15);
        flex-wrap: wrap;
        gap: 1rem;
    }

    .tasks-header h3 {
        font-size: 1.5rem;
        font-weight: 600;
        color: #1a1a2e;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.7rem;
    }

    .tasks-header h3 i {
        color: #10b981;
    }

    .tasks-header .task-count {
        background: #f0f2f5;
        padding: 0.3rem 1.2rem;
        border-radius: 50px;
        font-size: 0.85rem;
        color: #10b981;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }

    /* Add Task Button - Floating Action Button */
    #add-item {
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        z-index: 100;
    }

    #add-item p {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, #10b981, #34d399);
        color: white;
        border-radius: 50%;
        font-size: 2.5rem;
        font-weight: 300;
        text-decoration: none;
        box-shadow: 0 8px 30px rgba(16, 185, 129, 0.4);
        transition: all 0.3s ease;
        border: none;
        outline: none;
        margin: 0;
        cursor: pointer;
    }

    #add-item p:hover {
        transform: scale(1.1) rotate(90deg);
        box-shadow: 0 12px 40px rgba(16, 185, 129, 0.6);
        background: linear-gradient(135deg, #059669, #10b981);
    }

    /* Task Cards */
    .task-card {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        border: 1px solid rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        box-shadow: 0 2px 10px rgba(0,  0, 0, 0.02);
        margin-bottom: 1rem;
        position: relative;
        overflow: hidden;
    }

    .task-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #10b981, #34d399);
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .task-card:hover::before {
        opacity: 1;
    }

    .task-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.06);
        border-color: rgba(16, 185, 129, 0.15);
    }

    .task-card:last-child {
        margin-bottom: 0;
    }

    .task-card .task-header {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-bottom: 0.8rem;
    }

    .task-card .task-content {
        color: #1a1a2e;
        font-size: 0.95rem;
        line-height: 1.6;
        margin-bottom: 0.8rem;
        padding: 0.8rem 1rem;
        background: #f8fafc;
        border-radius: 12px;
        border-left: 3px solid #10b981;
    }

    /* File Attachment Styles */
    .task-card .task-attachment {
        margin: 0.8rem 0;
        padding: 0.8rem 1rem;
        background: #f0fdf4;
        border-radius: 12px;
        border: 1px solid #d1fae5;
        display: flex;
        align-items: center;
        gap: 0.8rem;
        transition: all 0.3s ease;
    }

    .task-card .task-attachment:hover {
        background: #dcfce7;
        border-color: #10b981;
    }

    .task-card .task-attachment i {
        font-size: 1.5rem;
        color: #10b981;
    }

    .task-card .task-attachment .file-info {
        flex: 1;
    }

    .task-card .task-attachment .file-info .file-name {
        font-weight: 500;
        color: #1a1a2e;
        font-size: 0.9rem;
    }

    .task-card .task-attachment .file-info .file-size {
        font-size: 0.75rem;
        color: #6b7280;
    }

    .task-card .task-attachment .btn-download {
        background: #10b981;
        color: white;
        border: none;
        padding: 0.3rem 1rem;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
    }

    .task-card .task-attachment .btn-download:hover {
        background: #059669;
        transform: translateY(-2px);
    }

    .task-card .task-meta {
        display: flex;
        gap: 1.5rem;
        flex-wrap: wrap;
        font-size: 0.8rem;
        color: #6b7280;
        padding-top: 0.8rem;
        border-top: 1px solid rgba(0, 0, 0, 0.03);
    }

    .task-card .task-meta span {
        display: flex;
        align-items: center;
        gap: 0.3rem;
    }

    .task-card .task-meta span i {
        color: #10b981;
    }

    .task-card .task-actions {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .task-card .task-actions .btn-action {
        padding: 0.3rem 1rem;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 500;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
    }

    .btn-action.btn-edit {
        background: #dbeafe;
        color: #1e40af;
    }

    .btn-action.btn-edit:hover {
        background: #bfdbfe;
        transform: translateY(-2px);
    }

    .btn-action.btn-delete {
        background: #fee2e2;
        color: #991b1b;
    }

    .btn-action.btn-delete:hover {
        background: #fecaca;
        transform: translateY(-2px);
    }

    /* Empty State */
    .empty-tasks {
        text-align: center;
        padding: 4rem 2rem;
        background: white;
        border-radius: 24px;
        border: 1px solid rgba(0, 0, 0, 0.03);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
    }

    .empty-tasks i {
        font-size: 4rem;
        color: #d1d5db;
        display: block;
        margin-bottom: 1rem;
    }

    .empty-tasks h4 {
        color: #6b7280;
        margin-bottom: 0.5rem;
    }

    .empty-tasks p {
        color: #9ca3af;
        margin-bottom: 1.5rem;
    }

    .empty-tasks .btn-add-task {
        background: linear-gradient(135deg, #10b981, #34d399);
        color: white;
        border: none;
        padding: 0.6rem 2rem;
        border-radius: 50px;
        font-weight: 600;
        transition: all 0.3s ease;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .empty-tasks .btn-add-task:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
    }

    /* Modal Styling */
    .modal-content {
        border-radius: 20px;
        border: none;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
        overflow: hidden;
    }

    .modal-header {
        background: linear-gradient(135deg, #1a1a2e, #16213e);
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        padding: 1.2rem 1.5rem;
    }

    .modal-header .modal-title {
        font-weight: 700;
        color: white;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .modal-header .modal-title i {
        color: #10b981;
    }

    .modal-header .btn-close {
        filter: brightness(0) invert(1);
        opacity: 0.6;
    }

    .modal-header .btn-close:hover {
        opacity: 1;
    }

    .modal-body {
        padding: 1.5rem;
    }

    .modal-body label {
        font-weight: 600;
        color: #1a1a2e;
        margin-bottom: 0.4rem;
        display: block;
    }

    .modal-body label.required::after {
        content: ' *';
        color: #ef4444;
        font-weight: 700;
    }

    .modal-body .form-control {
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        padding: 0.7rem 1rem;
        font-size: 0.95rem;
        transition: all 0.3s ease;
        background: #f9fafb;
        width: 100%;
        resize: vertical;
        min-height: 100px;
    }

    .modal-body .form-control:focus {
        border-color: #10b981;
        background: white;
        box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
        outline: none;
    }

    .modal-body .form-control.is-invalid {
        border-color: #ef4444;
        background: #fef2f2;
    }

    .modal-body .invalid-feedback {
        color: #ef4444;
        font-size: 0.8rem;
        margin-top: 0.3rem;
        display: none;
    }

    .modal-body .invalid-feedback.show {
        display: block;
    }

    /* File Upload Styles */
    .file-upload-wrapper {
        border: 2px dashed #e5e7eb;
        border-radius: 12px;
        padding: 1.5rem;
        text-align: center;
        transition: all 0.3s ease;
        cursor: pointer;
        background: #f9fafb;
        position: relative;
    }

    .file-upload-wrapper:hover {
        border-color: #10b981;
        background: #f0fdf4;
    }

    .file-upload-wrapper.dragover {
        border-color: #10b981;
        background: #f0fdf4;
        transform: scale(1.02);
    }

    .file-upload-wrapper i {
        font-size: 2.5rem;
        color: #10b981;
        display: block;
        margin-bottom: 0.5rem;
    }

    .file-upload-wrapper p {
        color: #6b7280;
        font-size: 0.9rem;
        margin: 0;
    }

    .file-upload-wrapper .file-types {
        color: #9ca3af;
        font-size: 0.75rem;
        margin-top: 0.3rem;
    }

    .file-upload-wrapper input[type="file"] {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        cursor: pointer;
    }

    .file-preview {
        display: none;
        margin-top: 1rem;
        padding: 0.8rem 1rem;
        background: #f0fdf4;
        border-radius: 12px;
        border: 1px solid #d1fae5;
        align-items: center;
        gap: 0.8rem;
    }

    .file-preview.show {
        display: flex;
    }

    .file-preview i {
        font-size: 1.5rem;
        color: #10b981;
    }

    .file-preview .file-preview-info {
        flex: 1;
    }

    .file-preview .file-preview-info .file-preview-name {
        font-weight: 500;
        color: #1a1a2e;
        font-size: 0.9rem;
    }

    .file-preview .file-preview-info .file-preview-size {
        font-size: 0.75rem;
        color: #6b7280;
    }

    .file-preview .btn-remove-file {
        background: #fee2e2;
        color: #991b1b;
        border: none;
        padding: 0.2rem 0.6rem;
        border-radius: 8px;
        font-size: 0.8rem;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .file-preview .btn-remove-file:hover {
        background: #fecaca;
    }

    /* Existing file in modal */
    .existing-file {
        display: none;
        margin-top: 1rem;
        padding: 0.8rem 1rem;
        background: #f0fdf4;
        border-radius: 12px;
        border: 1px solid #d1fae5;
        align-items: center;
        gap: 0.8rem;
    }

    .existing-file.show {
        display: flex;
    }

    .existing-file i {
        font-size: 1.5rem;
        color: #10b981;
    }

    .existing-file .existing-file-info {
        flex: 1;
    }

    .existing-file .existing-file-info .existing-file-name {
        font-weight: 500;
        color: #1a1a2e;
        font-size: 0.9rem;
    }

    .existing-file .existing-file-info .existing-file-size {
        font-size: 0.75rem;
        color: #6b7280;
    }

    .existing-file .btn-remove-file {
        background: #fee2e2;
        color: #991b1b;
        border: none;
        padding: 0.2rem 0.6rem;
        border-radius: 8px;
        font-size: 0.8rem;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .existing-file .btn-remove-file:hover {
        background: #fecaca;
    }

    .btn-save-modal {
        background: linear-gradient(135deg, #10b981, #34d399);
        color: white;
        border: none;
        padding: 0.6rem 2.5rem;
        border-radius: 50px;
        font-size: 0.95rem;
        font-weight: 600;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-save-modal:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
        background: linear-gradient(135deg, #059669, #10b981);
    }

    .btn-save-modal i {
        font-size: 1.1rem;
    }

    /* Delete Modal */
    .modal-body .delete-icon {
        font-size: 4rem;
        color: #ef4444;
        display: block;
        margin-bottom: 1rem;
        text-align: center;
    }

    .modal-body .delete-text {
        text-align: center;
    }

    .modal-body .delete-text h4 {
        font-weight: 600;
        color: #1a1a2e;
        margin-bottom: 0.5rem;
    }

    .modal-body .delete-text p {
        color: #6b7280;
        font-size: 0.9rem;
    }

    .btn-yes {
        background: linear-gradient(135deg, #ef4444, #dc2626);
        color: white;
        border: none;
        padding: 0.5rem 1.5rem;
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-yes:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(239, 68, 68, 0.3);
        color: white;
    }

    .btn-no {
        background: #f0f2f5;
        color: #1a1a2e;
        border: none;
        padding: 0.5rem 1.5rem;
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-no:hover {
        background: #e5e7eb;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .tasks-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .task-card .task-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .task-card .task-actions {
            width: 100%;
        }

        .task-card .task-actions .btn-action {
            flex: 1;
            justify-content: center;
        }

        #add-item p {
            width: 50px;
            height: 50px;
            font-size: 2rem;
        }

        #add-item {
            bottom: 1rem;
            right: 1rem;
        }
    }

    @media (max-width: 480px) {
        .task-card {
            padding: 1rem;
        }

        .task-card .task-content {
            padding: 0.6rem 0.8rem;
            font-size: 0.9rem;
        }

        .task-card .task-meta {
            gap: 0.8rem;
            font-size: 0.75rem;
        }

        .modal-body {
            padding: 1rem;
        }

        .modal-body .form-control {
            min-height: 80px;
        }

        .btn-save-modal {
            width: 100%;
            justify-content: center;
        }

        .task-card .task-attachment {
            flex-direction: column;
            text-align: center;
        }
    }
</style>

<!-- ===== MAIN TASKS CONTENT ===== -->
<div class="tasks-wrapper">
    <!-- Tasks Header -->
    <div class="tasks-header">
        <h3>
            <i class="bi bi-list-check"></i> Tasks
        </h3>
        <span class="task-count">
            <i class="bi bi-check-circle-fill"></i> 
            <?php 
                preg_match_all('/<div class="task-card"/', $tasksHtml, $matches);
                echo isset($matches[0]) ? count($matches[0]) : 0;
            ?> Total
        </span>
    </div>

    <!-- Tasks List -->
    <section id="tasks">
        <?php 
        if (!empty($tasksHtml) && trim($tasksHtml) !== '') {
            echo $tasksHtml;
        } else { 
        ?>
            <div class="empty-tasks">
                <i class="bi bi-clipboard-plus"></i>
                <h4>No Tasks Added</h4>
                <p>You haven't added any tasks to this project yet.</p>
                <button class="btn-add-task" data-bs-toggle="modal" data-bs-target="#addItem">
                    <i class="bi bi-plus-circle-fill"></i> Add Your First Task
                </button>
            </div>
        <?php } ?>
    </section>
</div>

<!-- Add Task Button (FAB) -->
<section id="add-item">
    <p data-bs-toggle="modal" data-bs-target="#addItem">
        <i class="bi bi-plus"></i>
    </p>
</section>

<!-- Add/Edit Task Modal -->
<div class="modal fade" id="addItem" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="addItemLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addItemLabel">
                    <i class="bi bi-plus-circle-fill"></i> New Task
                </h5>
                <button type="button" class="btn-close" id="close-save-task" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="task" class="required">Task Description</label>
                    <textarea id="task" class="form-control resizable" placeholder="Enter task description..." rows="4"></textarea>
                    <div class="invalid-feedback">
                        <i class="bi bi-exclamation-circle-fill"></i> Please provide a task description.
                    </div>
                </div>

                <!-- File Upload -->
                <div class="form-group mt-3">
                    <label>Attach File <span style="color: #6b7280; font-weight: 400; font-size: 0.85rem;">(optional)</span></label>
                    
                    <!-- Existing file (for edit mode) -->
                    <div class="existing-file" id="existingFile">
                        <i class="bi bi-file-earmark-fill"></i>
                        <div class="existing-file-info">
                            <div class="existing-file-name" id="existingFileName">file.pdf</div>
                            <div class="existing-file-size" id="existingFileSize">1.2 MB</div>
                        </div>
                        <button type="button" class="btn-remove-file" id="removeExistingFile">
                            <i class="bi bi-x-circle-fill"></i> Remove
                        </button>
                    </div>

                    <!-- File upload area -->
                    <div class="file-upload-wrapper" id="fileUploadWrapper">
                        <i class="bi bi-cloud-upload"></i>
                        <p><strong>Click to upload</strong> or drag and drop</p>
                        <div class="file-types">Accepted files: PDF, DOC, DOCX, XLS, XLSX, PNG, JPG (Max 10MB)</div>
                        <input type="file" id="taskFile" accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg">
                    </div>

                    <!-- File preview -->
                    <div class="file-preview" id="filePreview">
                        <i class="bi bi-file-earmark-fill"></i>
                        <div class="file-preview-info">
                            <div class="file-preview-name" id="filePreviewName">file.pdf</div>
                            <div class="file-preview-size" id="filePreviewSize">1.2 MB</div>
                        </div>
                        <button type="button" class="btn-remove-file" id="removeFile">
                            <i class="bi bi-x-circle-fill"></i> Remove
                        </button>
                    </div>
                </div>

                <div class="text-center my-4">
                    <button class="btn-save-modal" data-project-id="<?php echo $projectId; ?>" data-employee-id="<?php echo $employeeId; ?>" id="save-task">
                        <i class="bi bi-check-circle-fill"></i> Save Task
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Task Modal -->
<div class="modal fade" id="deleteItem" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="deleteItemLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #fef2f2, #fee2e2);">
                <h5 class="modal-title" id="deleteItemLabel" style="color: #991b1b;">
                    <i class="bi bi-trash3-fill"></i> Delete Task
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <span class="delete-icon">
                    <i class="bi bi-exclamation-circle-fill"></i>
                </span>
                <div class="delete-text">
                    <h4 id="deleteItemBody">Are you sure you want to delete this task?</h4>
                    <p>This action cannot be undone. All associated data will be permanently removed.</p>
                </div>
                <div class="d-flex justify-content-center gap-3 mt-4">
                    <button class="btn btn-no" data-bs-dismiss="modal" id="close-delete-task">
                        <i class="bi bi-x-circle"></i> Cancel
                    </button>
                    <button class="btn btn-yes" id="delete-task">
                        <i class="bi bi-check-circle"></i> Yes, Delete
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ===== JAVASCRIPT FOR TASK MANAGEMENT ===== -->
<script>
$(document).ready(function() {
    console.log('Task management JavaScript loaded');
    
    // ===== FILE UPLOAD HANDLING =====
    let selectedFile = null;
    let isFileRemoved = false;

    // ===== FILE INPUT CHANGE - THIS IS WHERE THE PREVIEW SHOULD WORK =====
    $('#taskFile').on('change', function() {
        console.log('File input changed');
        const file = this.files[0];
        console.log('Selected file:', file);
        
        if (file) {
            // Validate file size (10MB max)
            if (file.size > 10 * 1024 * 1024) {
                if (typeof baseControl !== 'undefined') {
                    baseControl.showToast('File size exceeds 10MB limit.');
                } else {
                    alert('File size exceeds 10MB limit.');
                }
                this.value = '';
                return;
            }
            
            // Validate file type
            const allowedTypes = ['application/pdf', 'application/msword', 
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'image/png', 'image/jpeg'];
            
            // Also check by extension for better compatibility
            const allowedExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'png', 'jpg', 'jpeg'];
            const fileExtension = file.name.split('.').pop().toLowerCase();
            
            if (!allowedTypes.includes(file.type) && !allowedExtensions.includes(fileExtension)) {
                if (typeof baseControl !== 'undefined') {
                    baseControl.showToast('Invalid file type. Please upload PDF, DOC, DOCX, XLS, XLSX, PNG, or JPG.');
                } else {
                    alert('Invalid file type. Please upload PDF, DOC, DOCX, XLS, XLSX, PNG, or JPG.');
                }
                this.value = '';
                return;
            }
            
            selectedFile = file;
            isFileRemoved = false;
            showFilePreview(file);
        } else {
            console.log('No file selected');
        }
    });

    // ===== DRAG AND DROP =====
    const $dropZone = $('#fileUploadWrapper');
    
    $dropZone.on('dragover', function(e) {
        e.preventDefault();
        $(this).addClass('dragover');
    });

    $dropZone.on('dragleave', function(e) {
        e.preventDefault();
        $(this).removeClass('dragover');
    });

    $dropZone.on('drop', function(e) {
        e.preventDefault();
        $(this).removeClass('dragover');
        console.log('File dropped');
        
        const file = e.originalEvent.dataTransfer.files[0];
        if (file) {
            $('#taskFile')[0].files = e.originalEvent.dataTransfer.files;
            $('#taskFile').trigger('change');
        }
    });

    // ===== SHOW FILE PREVIEW =====
    function showFilePreview(file) {
        console.log('Showing file preview for:', file.name);
        const size = (file.size / 1024 / 1024).toFixed(2) + ' MB';
        $('#filePreviewName').text(file.name);
        $('#filePreviewSize').text(size);
        $('#filePreview').addClass('show');
        $('#fileUploadWrapper').hide();
        $('#existingFile').removeClass('show');
        console.log('File preview shown');
    }

    // ===== REMOVE FILE =====
    $('#removeFile').on('click', function() {
        console.log('Removing file');
        selectedFile = null;
        isFileRemoved = true;
        $('#taskFile').val('');
        $('#filePreview').removeClass('show');
        $('#fileUploadWrapper').show();
    });

    // ===== REMOVE EXISTING FILE (EDIT MODE) =====
    $('#removeExistingFile').on('click', function() {
        console.log('Removing existing file');
        isFileRemoved = true;
        $('#existingFile').removeClass('show');
        $('#fileUploadWrapper').show();
        $('#save-task').data('remove-file', true);
    });

    // ===== DELETE TASK ATTEMPT =====
    $(document).on('click', '.delete-task-attempt', function() {
        const buttonId = $(this).attr('id');
        const taskId = buttonId ? buttonId.replace('task-', '') : null;
        
        console.log('Delete button clicked. Button ID:', buttonId);
        console.log('Extracted Task ID:', taskId);
        
        if (taskId) {
            $('#delete-task').data('task-id', taskId);
            console.log('Task ID set on delete button:', $('#delete-task').data('task-id'));
        } else {
            console.error('Could not extract task ID from button:', buttonId);
        }
    });

    // ===== DELETE TASK =====
    $(document).on('click', '#delete-task', function() {
        const $btn = $(this);
        const taskId = $btn.data('task-id');
        
        console.log('Delete button clicked. Task ID from data:', taskId);
        
        if (!taskId) {
            if (typeof baseControl !== 'undefined') {
                baseControl.showToast('Error: Task ID not found.');
            }
            return;
        }
        
        const originalText = $btn.html();
        $btn.html('<span class="spinner-border spinner-border-sm" role="status"></span> Deleting...');
        $btn.prop('disabled', true);
        
        $.ajax({
            url: 'utils.php',
            type: 'POST',
            data: {
                task: 'deleteTask',
                taskId: taskId
            },
            dataType: 'json',
            success: function(response) {
                console.log('Delete response:', response);
                $btn.html(originalText);
                $btn.prop('disabled', false);
                
                if (response.status === 'SUCCESS') {
                    if (typeof baseControl !== 'undefined') {
                        baseControl.showToast('Task deleted successfully!');
                    }
                    $('#close-delete-task').click();
                    setTimeout(function() {
                        location.reload();
                    }, 500);
                } else {
                    if (typeof baseControl !== 'undefined') {
                        baseControl.showToast('Failed to delete task: ' + (response.message || ''));
                    }
                }
            },
            error: function(xhr) {
                console.error('AJAX Error:', xhr);
                $btn.html(originalText);
                $btn.prop('disabled', false);
                if (typeof baseControl !== 'undefined') {
                    baseControl.showToast('Error deleting task. Please try again.');
                }
            }
        });
    });

    // ===== SAVE TASK =====
    $('#save-task').on('click', function() {
        const $btn = $(this);
        const $task = $('#task');
        const task = $task.val().trim();
        const projectId = $btn.data('project-id');
        const employeeId = $btn.data('employee-id');
        const taskId = $btn.data('task-id') || null;
        const removeFile = $btn.data('remove-file') || false;
        
        console.log('Saving task:', { task, projectId, employeeId, taskId, selectedFile: selectedFile ? selectedFile.name : 'None' });
        
        if (!task) {
            $task.addClass('is-invalid');
            $task.siblings('.invalid-feedback').addClass('show');
            if (typeof baseControl !== 'undefined') {
                baseControl.showToast('Please provide a task description.');
            }
            return;
        }
        
        $task.removeClass('is-invalid');
        $task.siblings('.invalid-feedback').removeClass('show');
        
        const originalText = $btn.html();
        $btn.html('<span class="spinner-border spinner-border-sm" role="status"></span> Saving...');
        $btn.prop('disabled', true);
        
        const formData = new FormData();
        
        if (taskId) {
            formData.append('task', 'editTask');
            formData.append('taskId', taskId);
        } else if (selectedFile) {
            formData.append('task', 'saveTaskWithFile');
        } else {
            formData.append('task', 'saveTask');
        }
        
        formData.append('employeeId', employeeId);
        formData.append('projectId', projectId);
        formData.append('employeeTask', task);
        
        if (selectedFile) {
            formData.append('file', selectedFile);
            console.log('File attached:', selectedFile.name, selectedFile.size, selectedFile.type);
        }
        
        if (removeFile) {
            formData.append('removeFile', '1');
        }
        
        // Log what we're sending
        for (let pair of formData.entries()) {
            console.log(pair[0] + ':', pair[1]);
        }
        
        $.ajax({
            url: 'utils.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                console.log('Save response:', response);
                $btn.html(originalText);
                $btn.prop('disabled', false);
                
                if (response.status === 'SUCCESS') {
                    if (typeof baseControl !== 'undefined') {
                        baseControl.showToast('Task saved successfully!');
                    }
                    selectedFile = null;
                    isFileRemoved = false;
                    $('#save-task').removeData('remove-file');
                    $('#close-save-task').click();
                    setTimeout(function() {
                        location.reload();
                    }, 500);
                } else {
                    if (typeof baseControl !== 'undefined') {
                        baseControl.showToast('Failed to save task: ' + (response.message || ''));
                    }
                }
            },
            error: function(xhr) {
                console.error('AJAX Error:', xhr);
                console.error('Response Text:', xhr.responseText);
                $btn.html(originalText);
                $btn.prop('disabled', false);
                if (typeof baseControl !== 'undefined') {
                    baseControl.showToast('Error saving task. Please try again.');
                }
            }
        });
    });

    // ===== EDIT TASK =====
    $(document).on('click', '.edit-task', function() {
        const $card = $(this).closest('.task-card');
        const taskId = $(this).data('task-id');
        const taskDescription = $card.find('.task-content').text().trim();
        
        console.log('Edit task ID:', taskId);
        
        $('#task').val(taskDescription);
        $('#save-task').data('task-id', taskId);
        $('#save-task').data('remove-file', false);
        $('#addItemLabel').html('<i class="bi bi-pencil-fill"></i> Edit Task');
        
        const $attachment = $card.find('.task-attachment');
        if ($attachment.length > 0) {
            const fileName = $attachment.find('.file-name').text();
            const fileSize = $attachment.find('.file-size').text();
            const fileId = $attachment.data('file-id');
            
            $('#existingFileName').text(fileName);
            $('#existingFileSize').text(fileSize);
            $('#existingFile').data('file-id', fileId);
            $('#existingFile').addClass('show');
            $('#fileUploadWrapper').hide();
            $('#filePreview').removeClass('show');
        } else {
            $('#existingFile').removeClass('show');
            $('#fileUploadWrapper').show();
            $('#filePreview').removeClass('show');
        }
        
        $('#addItem').modal('show');
    });

    // ===== RESET MODAL ON CLOSE =====
    $('#addItem').on('hidden.bs.modal', function() {
        console.log('Modal closed, resetting');
        $('#task').val('');
        $('#task').removeClass('is-invalid');
        $('.invalid-feedback').removeClass('show');
        $('#addItemLabel').html('<i class="bi bi-plus-circle-fill"></i> New Task');
        $('#save-task').removeData('task-id');
        $('#save-task').removeData('remove-file');
        $('#existingFile').removeClass('show');
        $('#fileUploadWrapper').show();
        $('#filePreview').removeClass('show');
        $('#taskFile').val('');
        selectedFile = null;
        isFileRemoved = false;
    });
});
</script>