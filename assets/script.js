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


});