class AdminControl{
    constructor(){
        this.toggleRoleState();
        this.addFocus();
    }

    toggleRoleState(){
        $('#save-or-edit').click(function(){
            const command = $(this).data('command');
            const value = $('#role-input').val();
            if(value){
                if(command === 'save'){
                    $(this).data('command','edit');
                    $('#role-input').attr('disabled',true);
                    $(this).text("Edit");
                }else{
                    $(this).data('command','save');
                    $('#role-input').attr('disabled',false);
                    $(this).text("Save");
                }
                $('#role-error').data('error',false);
                $('#role-error').text("");
            }
            else{
                $('#role-error').text("You need to put in a role.");
                $('#role-error').data('error',true);
            }
        });
    }

    addFocus(){
        $('#add-focus').click(function(){
            const focus = $('#focus-input').val();
            if(focus){
                $('#focus-error').text('');
                $('#close-add-focus-modal').click();
                $('#focus-input').val('');
            }
            else{
                $('#focus-error').text('Please add a focus.');
            }
        });
    }
}

const adminControl = new AdminControl();