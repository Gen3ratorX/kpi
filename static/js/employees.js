class EmployeeControl{
    constructor(){
        this.url = 'utils.php';
        this.saveTask();
        this.editTask();
        this.deleteTaskAttempt();
        this.deleteTask();
        // Assessment
        this.assessEmployeeAttempt();
        this.closeAssessment();
    }

    saveTask(){
        const inst = this;
        $('#save-task').click(function(){
            const isValidated = baseControl.validateFields(['#task']);
            const editTask = $(this).data('editTask');
            const taskId = $(this).data('taskId');
            if(isValidated){
                const employeeTask = $('#task').val();
                const employeeId = $(this).data('employeeId');
                const projectId = $(this).data('projectId');
                const success = function(res,statusCode,status){
                    // Close modal
                    $('#close-save-task').click();
                    // Editing task
                    if(editTask){
                        $(`.card-body#task-${taskId}`).text(employeeTask);
                    }
                    // New task
                    else{
                        // const taskId = $('.task').length + 1;
                        const taskId = res.taskId;
                        const tasksCount = $('.task').length;
                        if(tasksCount === 0){
                            $('#tasks').empty();
                        }
                        $('#tasks').append(
                            `<div class="action-item card shadow-sm mb-3 task">
                                <div class='options d-flex align-items-center justify-content-center'>
                                    <div>
                                        <button class='btn btn-md btn-2 me-2 delete-task-attempt' id='task-${taskId}' data-bs-toggle='modal' data-bs-target='#deleteItem'>Delete</button>
                                        <button type="button" data-task-id="task-${taskId}" class='btn btn-md btn-4 edit-task' data-bs-toggle='modal' data-bs-target='#addItem'>Edit</button>
                                    </div>
                                </div>
                                <div class="card-body" id="task-${taskId}">
                                    ${employeeTask}
                                </div>
                            </div>`
                        );
                        // Increase number of tasks
                        $('#no-of-tasks').text(tasksCount + 1);
                        // Add events
                        baseControl.toggleEventState('.edit-task',inst.editTask) // Edit
                        baseControl.toggleEventState('.delete-task-attempt',inst.deleteTaskAttempt) // Delete
                    }
                    // Clear
                    $('#task').val('');
                    $('#save-task').removeData();
                }
                const data = {
                    employeeTask,
                    editTask,
                    employeeId,
                    projectId,
                    task: editTask ? 'editTask' : 'saveTask',
                    taskId,
                }
                baseControl.fetchData(inst.url,data,"Save Task",'#save-task',false,success);
            }
        });
    }

    editTask(){
        $('.edit-task').click(function(){
            const taskId = $(this).data('taskId');
            const task  = $(`.card-body#${taskId}`).text().trim();
            $('#save-task').data({
                editTask: true,
                taskId: taskId.split('-')[1],
            });
            $('#task').val(task);
        });
    }

    deleteTaskAttempt(){
        $('.delete-task-attempt').click(function(){
            const taskId = $(this).attr('id').split('-')[1];
            $('#delete-task').data('taskId',taskId);
        });
    }

    deleteTask(){
        const inst = this;
        $('#delete-task').click(function(){
            const taskId = $(this).data('taskId');
            const data = {
                taskId,
                task: 'deleteTask'
            }
            const success = (res,statusCode,status) => {
                // Close modal
                $('#close-delete-task').click();
                // Remove task
                $(`button#task-${taskId}`).parents('.task').hide(function(){
                    const taskCount = $('.task').length;
                    if(taskCount - 1 == 0){
                        $('#tasks').empty();
                        $('#tasks').append(
                            `<div class="no-item">
                                No task has been added...
                                <div class="text-center">
                                    <button data-bs-toggle="modal" data-bs-target="#addItem" type="button" class="btn btn-md btn-1">Add Task</button>
                                </div>
                            </div>`
                        );
                    }
                    else{
                        $(this).remove();
                    }
                    // Decrease task
                    $('#no-of-tasks').text(taskCount - 1);
                });
            }

            baseControl.fetchData(inst.url,data,'Yes','#delete-task',false,success);
        });
    }

    // Assessment
    assessEmployeeAttempt(){
        const inst = this;
        $('.assess-employee').click(function(){
            const employeeData = $(this).data();
            console.log(employeeData);
            const success = (res,statusCode,status) => {
                console.log(res);
                $('#assessment-attempt-wrapper').slideUp(function(){
                    $('#assessment-wrapper').slideDown();
                });
            }
            const data = {
                ...employeeData,
                task: 'getTaskAssessments'
            };

            baseControl.fetchData(inst.url,data,'','',true,success);

        });
    }

    closeAssessment(){
        $('#close-assessment').click(function(){
            $('#assessment-wrapper').slideUp(function(){
                $('#assessment-attempt-wrapper').slideDown();
            });
        });
    }
}

const employeeControl = new EmployeeControl();