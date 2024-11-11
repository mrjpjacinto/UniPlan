function toggleDescription(eventId) {
    
    const description = document.getElementById(eventId);
    
   
    if (description.style.display === "none" || description.style.display === "") {
        description.style.display = "block"; 
    } else {
        description.style.display = "none"; 
    }
}

