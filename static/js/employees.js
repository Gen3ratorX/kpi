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
    generateRatings(rating = null){
        const ratings = [10,20,30,40,50,60,70,80,90,100];
        let ratingsHtml = "";
        for(let i of ratings){
            if(i === rating){
                ratingsHtml += `
                    <div class="col-auto">
                        <p class="rating selected-rating">${i}%</p>
                    </div>
                `;
            }
            else{
                ratingsHtml += `
                    <div class="col-auto">
                        <p class="rating">${i}%</p>
                    </div>
                `;
            }
        }
        return ratingsHtml;
    }

    assessEmployeeAttempt(){
        const inst = this;
        $('.assess-employee').click(function(){
            const employeeData = $(this).data();
            // console.log(employeeData);
            const success = (res,statusCode,status) => {
                // console.log(res);
                // Tasks added
                if(res.assessments.length > 0){
                    $('#assessment-wrapper').prepend(
                        `<div class='accordion' id='assessmentsAccordion'>
                            ${
                                res.assessments.map(
                                    assessment => {
                                        // Employee has been assessed
                                        if(assessment.isAssessed){
                                            const {taskId,description,projectId,employeeId,assessorId,rating,comments} =  assessment;
                                            if(assessment.readOnly){
                                                return `
                                                    <div class="accordion-item" id="task-${taskId}">
                                                        <h2 class="accordion-header" id="heading${taskId}">
                                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse${taskId}" aria-expanded="false" aria-controls="collapse${taskId}">
                                                                ${description}
                                                            </button>
                                                        </h2>
                                                        <div id="collapse${taskId}" class="accordion-collapse collapse" aria-labelledby="heading${taskId}" data-bs-parent="#assessmentsAccordion">
                                                            <div class="accordion-body">
                                                                <section class="d-flex justify-content-between align-items-center">
                                                                Rating: 
                                                                    <h1 class="display-6 text-primary">
                                                                        <strong>${rating || 0}%</strong>
                                                                    </h1>
                                                                </section>
                                                                <section>
                                                                    <p class="m-0">Comments</p>
                                                                    <div class="lead text-secondary">
                                                                        <p class='text-center'>${ comments || 'No Comments'}</p>
                                                                    </div>
                                                                </section>
                                                            </div>
                                                        </div>
                                                    </div>
                                                `;
                                            }
                                            else{
                                                return `
                                                    <div class="accordion-item" id="task-${taskId}">
                                                        <h2 class="accordion-header" id="heading${taskId}">
                                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse${taskId}" aria-expanded="false" aria-controls="collapse${taskId}">
                                                                ${description}
                                                            </button>
                                                        </h2>
                                                        <div id="collapse${taskId}" class="accordion-collapse collapse" aria-labelledby="heading${taskId}" data-bs-parent="#assessmentsAccordion">
                                                            <div class="accordion-body">
                                                                <p class="alert alert-danger d-none error">You have to rate the task before saving.</p>
                                                                <div>
                                                                    <label for="" class="required">Rating:</label>
                                                                    <div class="row g-3">
                                                                        ${inst.generateRatings(rating)}
                                                                    </div>
                                                                </div>
                                                                <div class="mt-3">
                                                                    <label for="required">Comments:</label>
                                                                    <textarea class="form-control">${comments}</textarea>
                                                                </div>
                                                                <div class="text-center my-3">
                                                                    <button type="button" class="btn btn-1 btn-md save-assessment" data-task-id="task-${taskId}" data-assessor-id="${assessorId}">Save</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                `;
                                            }
                                        }
                                        // Employee has not been assessed
                                        else{
                                            const {taskId,description,projectId,employeeId,assessorId} =  assessment;
                                            // Not assessed and readonly
                                            if(assessment.readOnly){
                                                return `
                                                    <div class="accordion-item" id="task-${taskId}">
                                                        <h2 class="accordion-header" id="heading${taskId}">
                                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse${taskId}" aria-expanded="false" aria-controls="collapse${taskId}">
                                                                ${description}
                                                            </button>
                                                        </h2>
                                                        <div id="collapse${taskId}" class="accordion-collapse collapse" aria-labelledby="heading${taskId}" data-bs-parent="#assessmentsAccordion">
                                                            <div class="accordion-body">
                                                                <section class="d-flex justify-content-between align-items-center">
                                                                Rating: 
                                                                    <h1 class="display-6 text-primary">
                                                                        <strong>0%</strong>
                                                                    </h1>
                                                                </section>
                                                                <section>
                                                                    <p class="m-0">Comments</p>
                                                                    <div class="lead text-secondary">
                                                                        <p class='text-center'>No Comments</p>
                                                                    </div>
                                                                </section>
                                                            </div>
                                                        </div>
                                                    </div>
                                                `;
                                            }
                                            // Not assessed and not readonly
                                            else{
                                                return `
                                                    <div class="accordion-item" id="task-${taskId}">
                                                        <h2 class="accordion-header" id="heading${taskId}">
                                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse${taskId}" aria-expanded="false" aria-controls="collapse${taskId}">
                                                                ${description}
                                                            </button>
                                                        </h2>
                                                        <div id="collapse${taskId}" class="accordion-collapse collapse" aria-labelledby="heading${taskId}" data-bs-parent="#assessmentsAccordion">
                                                            <div class="accordion-body">
                                                                <p class="alert alert-danger d-none error">You have to rate the task before saving.</p>
                                                                <div>
                                                                    <label for="" class="required">Rating:</label>
                                                                    <div class="row g-3">
                                                                        ${inst.generateRatings()}
                                                                    </div>
                                                                </div>
                                                                <div class="mt-3">
                                                                    <label for="required">Comments:</label>
                                                                    <textarea class="form-control"></textarea>
                                                                </div>
                                                                <div class="text-center my-3">
                                                                    <button type="button" class="btn btn-1 btn-md save-assessment" data-task-id="task-${taskId}" data-assessor-id="${assessorId}">Save</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                `;
                                            }
                                        }
                                    }
                                ).join('')
                            }
                        </div>`
                    );
                    // Toggle event handler
                    baseControl.toggleEventState('.save-assessment',() => inst.saveAssessment());
                    baseControl.toggleEventState('.rating',() => inst.toggleRating());
                }
                // No tasks added
                else{
                    $('#assessment-wrapper').prepend(
                        `<div class="no-item">No Tasks have been added...</div>`
                    );
                }
                // Show assessments wrapper
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
                // Remove previous element
                $('#close-assessment').parent().prev().remove();
            });
        });
    }

    toggleRating(){
        $(".rating").click(function(){
            const isSelected = $(this).hasClass('selected-rating');
            // Get container id
            const containerId = $(this).parents('.accordion-item').attr('id');
            $(`#${containerId} .rating`).removeClass('selected-rating');
            if(!isSelected){
                $(this).addClass('selected-rating');
            }
        });
    }

    saveAssessment(){
        const inst = this;
        $('.save-assessment').click(function(){
            const taskId = $(this).data('taskId').split('-')[1];
            const assesorId = $(this).data('assessorId');
            let rating = $(`#${taskId}`).find('.selected-rating');
            const comments = $(`#${taskId}`).find('textarea').val();
            if(rating.length == 0 && comments){
                $(`#${taskId} .error`).removeClass('d-none');
            }
            else{
                $(`#${taskId} .error`).addClass('d-none');
                rating = $($(`#${taskId}`).find('.selected-rating')[0]).text();
                const data = {
                    assesorId,
                    taskId,
                    rating,
                    comments,
                    task: 'saveAssessment'
                }
                const success = (res,statusCode,status) => {
                    console.log(res);
                }
                baseControl.fetchData(inst.url,data,'Save','.save-assessment',false,success);
            }
        });
    }
}

const employeeControl = new EmployeeControl();