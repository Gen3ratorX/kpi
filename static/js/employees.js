class EmployeeControl{
    constructor(){
        this.addTask();
    }

    addTask(){
        $('#add-task').click(function(){
            console.log("Add task");
        });
    }
}

const employeeControl = new EmployeeControl();