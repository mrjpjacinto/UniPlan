document.addEventListener('DOMContentLoaded', () => {
    const toggleButtons = document.querySelectorAll('.toggle-comments');

    toggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            const commentsDiv = this.nextElementSibling; // Get the next sibling div (the comments)
            if (commentsDiv.style.display === "none" || commentsDiv.style.display === "") {
                commentsDiv.style.display = "block";
                this.textContent = "Hide Comment"; // Change button text
            } else {
                commentsDiv.style.display = "none";
                this.textContent = "Show Comment"; // Change button text
            }
        });
    });

    // Toggle the overall feedback section
    const overallFeedbackButton = document.getElementById('toggle-overall-feedback');
    const overallFeedbackDiv = document.getElementById('overall-feedback');

    overallFeedbackButton.addEventListener('click', () => {
        if (overallFeedbackDiv.style.display === "none" || overallFeedbackDiv.style.display === "") {
            overallFeedbackDiv.style.display = "block";
            overallFeedbackButton.textContent = "Hide Feedbacks"; // Change button text
        } else {
            overallFeedbackDiv.style.display = "none";
            overallFeedbackButton.textContent = "Show Feedbacks"; // Change button text
        }
    });
});
