class AdminControl{
    constructor(){
        this.pageInit();
        this.toggleRoleState();
        this.addFocusAttempt();
        this.saveFocus();
        this.deleteItem();
    }

    pageInit(){
        // const myModalAlternative = new bootstrap.Modal('#deleteFocusModal', {});
        // myModalAlternative.show();
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

    toggleEventState(ele,action,event='click'){
        $(ele).off(event);
        action();
    }

    addFocusAttempt(){
        $('.add-focus-attempt').click(function(){
            $('#save-focus').data('command','add');

        });
    }

    editFocusAttempt(){
        $('.edit-focus-attempt').click(function(){
            const focusIdentifier = $(this).data('focusIdentifier');
            // console.log(`Edit ${focusIdentifier}`)
            $('#save-focus').data({'command':'edit',focusIdentifier});
            // Get focus and update the focus modal with it
            const focus = $(this).parents('.accordion-item').find('.accordion-button').text().trim();
            $('#focus-input').val(focus);
        });
    }

    deleteFocusAttempt(){
        $('.delete-focus-attempt').click(function(){
            $('#deleteItemModalLabel').text("Delete Focus");
            $('#deleteItemModal').find('.modal-body > p').text("Do you want to delete this focus?");
            const focusIdentifier = $(this).data('focusIdentifier');
            $('#delete-item').data({'identifier':focusIdentifier,'item':'focus'});
        });
    }

    deleteItem(){
        const inst = this;
        $('#delete-item').click(function(){
            const {item,identifier} = $(this).data();
            if(item == 'focus'){
                $(`#${identifier}`).hide(function(){
                    if($('#focuses').children().length === 1){
                        $('#focuses').hide(function(){
                            $('#focus-wrapper').prev().after(
                                `<!-- No Focus -->
                                    <section class="no-item">
                                        No Focus Has Been Added
                                        <div>
                                            <a href="#" class="btn btn-1 btn-md add-focus-attempt" data-bs-toggle="modal" data-bs-target="#focusModal"> Add Focus </a>
                                        </div>
                                    </section>
                                `
                            );
                            // Add events to add focus button
                            inst.toggleEventState('.add-focus-attempt',inst.addFocusAttempt)
                            $(this).remove();
                        });
                    }
                    else{
                        $(this).remove();
                    }
                });
            }
            // Close modal
            $('#close-delete-item-modal').click();
        });
    }

    saveFocus(){
        const inst = this;
        $('#save-focus').click(function(){
            const focus = $('#focus-input').val();
            if(focus){
                const command = $(this).data('command');
                $('#focus-error').text('');
                $('#close-focus-modal').click();
                $('#focus-input').val('');
                // Add focus
                if(command === 'add'){
                    const focusCount = $('.focus').length;
                    const focusIdentifier = Math.round(Math.random() * 1000000);
                    if(focusCount > 0){
                        // Focus already presen
                        $('#focuses').append(
                            `<div class="accordion-item focus" id=focus-${focusIdentifier}>
                                <h2 class="accordion-header" id="panelsStayOpen-heading${focusIdentifier}">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapse${focusIdentifier}" aria-expanded="false" aria-controls="panelsStayOpen-collapse${focusIdentifier}">
                                        ${focus}
                                    </button>
                                </h2>
                                <div id="panelsStayOpen-collapse${focusIdentifier}" class="accordion-collapse collapse" aria-labelledby="panelsStayOpen-heading${focusIdentifier}">
                                    <div class="accordion-body">
                                        <div class="d-flex justify-content-end mb-2">
                                            <button class="btn btn-3 btn-md me-2">Add Objective</button>
                                            <button class="btn btn-4 btn-md me-2 edit-focus-attempt" data-focus-identifier="focus-${focusIdentifier}" data-bs-toggle="modal" data-bs-target="#focusModal">Edit Focus</button>
                                            <button class="btn btn-2 btn-md delete-focus-attempt" data-focus-identifier="focus-${focusIdentifier}" data-bs-toggle="modal" data-bs-target="#deleteItemModal">Delete Focus</button>
                                        </div>
                                        <div class="objectives">
                                            <!-- No Objective -->
                                            <section class="no-item">
                                                No Objective Has Been Added
                                                <div>
                                                    <a href="#" class="btn btn-1 btn-md" data-bs-toggle="modal" data-bs-target="#addFocusModal"> Add Objective </a>
                                                </div>
                                            </section>
                                        </div>
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
                            `<div class="accordion" id="focuses">
                                <div class="accordion-item focus" id="focus-${focusIdentifier}">
                                    <h2 class="accordion-header" id="panelsStayOpen-heading${focusIdentifier}">
                                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapse${focusIdentifier}" aria-expanded="true" aria-controls="panelsStayOpen-collapse${focusIdentifier}">
                                            ${focus}
                                        </button>
                                    </h2>
                                    <div id="panelsStayOpen-collapse${focusIdentifier}" class="accordion-collapse collapse show" aria-labelledby="panelsStayOpen-heading${focusIdentifier}">
                                        <div class="accordion-body">
                                            <div class="d-flex justify-content-end mb-2">
                                                <button class="btn btn-3 btn-md me-2">Add Objective</button>
                                                <button class="btn btn-4 btn-md me-2 edit-focus-attempt" data-focus-identifier="focus-${focusIdentifier}" data-bs-toggle="modal" data-bs-target="#focusModal">Edit Focus</button>
                                                <button class="btn btn-2 btn-md delete-focus-attempt" data-focus-identifier="focus-${focusIdentifier}" data-bs-toggle="modal" data-bs-target="#deleteItemModal">Delete Focus</button>
                                            </div>
                                            <div class="objectives">
                                                <!-- No Objective -->
                                                <section class="no-item">
                                                    No Objective Has Been Added
                                                    <div>
                                                        <a href="#" class="btn btn-1 btn-md" data-bs-toggle="modal" data-bs-target="#addFocusModal"> Add Objective </a>
                                                    </div>
                                                </section>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>`
                        );
                    }
                    // Add events to buttons
                    $('.edit-focus-attempt, .delete-focus-attempt').off('click');
                    inst.editFocusAttempt();
                    inst.deleteFocusAttempt();
                }
                // Update focus
                else{
                    // use identifier to update focus name
                    const focusIdentifier = $(this).data('focusIdentifier');
                    $(`#${focusIdentifier}`).find('.accordion-button').text(focus);
                }
                
            }
            else{
                $('#focus-error').text('Please add a focus.');
            }
        });
    }
}

const adminControl = new AdminControl();