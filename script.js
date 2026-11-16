function animateValue(obj, start, end, duration) {
    let startTimestamp = null;

    function step(timestamp) {
        if (!startTimestamp) startTimestamp = timestamp;

        const progress = Math.min((timestamp - startTimestamp) / duration, 1);

        obj.textContent = Math.floor(progress * (end - start) + start);

        if (progress < 1) {
            requestAnimationFrame(step);
        }
    }

    requestAnimationFrame(step);
}

// Elements
const value1 = document.getElementById("value1");
const value2 = document.getElementById("value2");

// Observe when they enter the viewport
const observer = new IntersectionObserver((entries, observer) => {

    entries.forEach(entry => {

        if (entry.isIntersecting) {

            if (entry.target.id === "value1") {
                animateValue(value1, 0, 18, 5000);
            }

            if (entry.target.id === "value2") {
                animateValue(value2, 0, 760, 5000);
            }

            // Stop observing so it only runs once
            observer.unobserve(entry.target);
        }

    });

}, {
    threshold: 0.5 // Start when 50% of the element is visible
});

// Start observing
observer.observe(value1);
observer.observe(value2);