const jobPostingForm = document.getElementById("jobPostingForm");

jobPostingForm.addEventListener("submit", async(e)=> 
{
  e.preventDefault();

  const formData = new FormData(jobPostingForm);
  const data = Object.fromEntries(formData.entries());

  console.log(data);

});