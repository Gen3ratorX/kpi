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
            const focusCount = $('.focus').length;
            console.log(focusCount)
            if(focus){
                $('#focus-error').text('');
                $('#close-add-focus-modal').click();
                $('#focus-input').val('');
                if(focusCount > 0){
                    // Focus already presen
                    $('#focuses').append(
                        `<div class="accordion-item focus">
                            <h2 class="accordion-header" id="panelsStayOpen-heading${focusCount + 1}">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapse${focusCount + 1}" aria-expanded="false" aria-controls="panelsStayOpen-collapse${focusCount + 1}">
                                    ${focus}
                                </button>
                            </h2>
                            <div id="panelsStayOpen-collapse${focusCount + 1}" class="accordion-collapse collapse" aria-labelledby="panelsStayOpen-heading${focusCount + 1}">
                                <div class="accordion-body">
                                    <strong>This is the third item's accordion body.</strong> It is hidden by default, until the collapse plugin adds the appropriate classes that we use to style each element. These classes control the overall appearance, as well as the showing and hiding via CSS transitions. You can modify any of this with custom CSS or overriding our default variables. It's also worth noting that just about any HTML can go within the <code>.accordion-body</code>, though the transition does limit overflow.
                                </div>
                            </div>
                        </div>
                    `
                    );
                }
                else{
                    // First focus
                    $('#focus-wrapper').prev().remove();
                    $('#focus-wrapper').append(
                        `<div class="accordion" id='focuses' id="accordionPanelsStayOpenExample">
                            <div class="accordion-item focus">
                                <h2 class="accordion-header" id="panelsStayOpen-heading${focusCount + 1}">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapse${focusCount + 1}" aria-expanded="true" aria-controls="panelsStayOpen-collapse${focusCount + 1}">
                                        ${focus}
                                    </button>
                                </h2>
                                <div id="panelsStayOpen-collapse${focusCount + 1}" class="accordion-collapse collapse show" aria-labelledby="panelsStayOpen-heading${focusCount + 1}">
                                    <div class="accordion-body">
                                        <strong>This is the first item's accordion body.</strong> It is shown by default, until the collapse plugin adds the appropriate classes that we use to style each element. These classes control the overall appearance, as well as the showing and hiding via CSS transitions. You can modify any of this with custom CSS or overriding our default variables. It's also worth noting that just about any HTML can go within the <code>.accordion-body</code>, though the transition does limit overflow.
                                    </div>
                                </div>
                            </div>
                        </div>`
                    );
                }
            }
            else{
                $('#focus-error').text('Please add a focus.');
            }
        });
    }
}

const adminControl = new AdminControl();