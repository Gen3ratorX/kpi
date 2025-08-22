<?php
$tasksHtml = $taskControl->generateEmployeeTasksHtml($employeeId, $projectId);
?>

<!-- Add item -->
<section id="add-item">
    <p data-bs-toggle="modal" data-bs-target="#addItem">+</p>
</section>

<!-- Add Item Modal -->
<div class="modal fade" id="addItem" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="addItemLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addItemLabel">Task</h5>
                <button type="button" class="btn-close" id="close-save-task" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <label for="task" class="required">Task</label>
                <textarea id="task" class="form-control resizable"></textarea>
                <div class="invalid-feedback">
                    Please provide a task.
                </div>
                <div class="text-center my-5">
                    <button class="btn btn-1 btn-md" data-project-id="<?php echo $projectId; ?>" data-employee-id="<?php echo $employeeId; ?>" id="save-task">Save</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Item Modal -->
<div class="modal fade" id="deleteItem" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="deleteItemLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteItemLabel">Delete Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h4 id="deleteItemBody" class="text-secondary">
                    Are you sure you want to delete this task?
                </h4>
                <div class="text-end mt-4">
                    <button class="btn btn-2 btn-sm" id="delete-task">Yes</button>
                    <button class="btn btn-3 btn-sm" data-bs-dismiss="modal" id="close-delete-task">No</button>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Tasks -->
<h3 class="text-secondary">Tasks</h3>
<section id='tasks'>
    <?php echo $tasksHtml; ?>
    <!-- <div class="no-item">
        No task has been added...
        <div class="text-center">
            <button data-bs-toggle="modal" data-bs-target="#addItem" type="button" class="btn btn-md btn-1">Add Task</button>
        </div>
    </div> -->
    <!-- <div class="action-item card shadow-sm mb-3 task">
        <div class='options d-flex align-items-center justify-content-center'>
            <div>
                <button class='btn btn-md btn-2 me-2 delete-task-attempt' id="task-1" data-bs-toggle='modal' data-bs-target='#deleteItem'>Delete</button>
                <button type="button" data-task-id="task-1" class='btn btn-md btn-4 edit-task' data-bs-toggle='modal' data-bs-target='#addItem'>Edit</button>
            </div>
        </div>
        <div class="card-body" id="task-1">
            Task 1 is all about god who is the author and finisher of our faith.
        </div>
    </div>
    <div class="action-item card shadow-sm mb-3 task">
        <div class='options d-flex align-items-center justify-content-center'>
            <div>
                <button class='btn btn-md btn-2 me-2 delete-task-attempt' id="task-2" data-bs-toggle='modal' data-bs-target='#deleteItem'>Delete</button>
                <button type="button" data-task-id="task-2" class='btn btn-md btn-4 edit-task' data-bs-toggle='modal' data-bs-target='#addItem'>Edit</button>
            </div>
        </div>
        <div class="card-body" id="task-2">
            Task 2 is all about Jesus Christ who came to earth to die for us.
        </div>
    </div> -->
</section>