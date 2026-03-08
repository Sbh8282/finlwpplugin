document.addEventListener("DOMContentLoaded", function () {

const steps = document.querySelectorAll(".step");
const formSteps = document.querySelectorAll(".form-step");
const nextBtns = document.querySelectorAll(".next-btn");
const prevBtns = document.querySelectorAll(".prev-btn");
const progress = document.querySelector(".progress");

let currentStep = 0;

/* SHOW STEP */
function showStep(index){

formSteps.forEach((step)=>{
step.classList.remove("active");
});

steps.forEach((step)=>{
step.classList.remove("active");
});

formSteps[index].classList.add("active");
steps[index].classList.add("active");

const progressPercent = (index/(steps.length-1))*100;
progress.style.width = progressPercent + "%";

currentStep = index;
}


/* NEXT BUTTON */
nextBtns.forEach(btn=>{
btn.addEventListener("click", function(){

if(currentStep < formSteps.length-1){
showStep(currentStep + 1);
}

});
});


/* PREVIOUS BUTTON */
prevBtns.forEach(btn=>{
btn.addEventListener("click", function(){

if(currentStep > 0){
showStep(currentStep - 1);
}

});
});


/* CLICK STEP NAVIGATION */
steps.forEach((step,index)=>{
step.addEventListener("click", function(){
showStep(index);
});
});

/* ACCOUNT CARD SELECTION */
const accountCards = document.querySelectorAll('.account-card');
accountCards.forEach(card => {
    card.addEventListener('click', function(e) {
        // Prevent radio button default behavior
        e.preventDefault();
        
        // Get the parent form step
        const formStep = this.closest('.form-step');
        if (!formStep) return;
        
        // Remove selected class from all cards in this form step
        formStep.querySelectorAll('.account-card').forEach(c => {
            c.classList.remove('selected');
        });
        
        // Add selected class to clicked card
        this.classList.add('selected');
        
        // Check the radio button inside       
        const radio = this.querySelector('input[type="radio"]');
        if (radio) {
            radio.checked = true;
        }
    });
});

/* FORM SUBMISSION */
const bankForm = document.getElementById('bankForm');
const corporateForm = document.getElementById('corporateForm');

if (bankForm) {
    bankForm.addEventListener('submit', function(e) {
        e.preventDefault();
        submitForm('bank');
    });
}

if (corporateForm) {
    corporateForm.addEventListener('submit', function(e) {
        e.preventDefault();
        submitForm('corporate');
    });
}

function submitForm(formType) {
    const form = formType === 'bank' ? bankForm : corporateForm;
    const formData = new FormData(form);
    
    // Add AJAX action and nonce
    if (formType === 'bank') {
        formData.append('action', 'submit_bank_form');
        formData.append('_wpnonce', bankFormAjax.bankNonce);
    } else {
        formData.append('action', 'submit_corporate_form');
        formData.append('_wpnonce', bankFormAjax.corporateNonce);
    }

    fetch(bankFormAjax.ajaxurl, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Application submitted successfully! Application ID: ' + data.data.post_id);
            form.reset();
            showStep(0);
        } else {
            alert('Error: ' + data.data);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error submitting form. Please try again.');
    });
}

});