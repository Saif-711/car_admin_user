setTimeout(() => {
        const msg = document.querySelector("div[style*='background-color: #d4edda']");
        if (msg) msg.style.display = "none";
    }, 3000);


  function revealOnScroll() {
    const section = document.getElementById('aboutMyPage');
    const features = section.querySelectorAll('.feature-box');
    const extras = section.querySelectorAll('.how-it-works, .call-to-action');

    const sectionTop = section.getBoundingClientRect().top;
    const trigger = window.innerHeight * 0.85;

    if (sectionTop < trigger) {
      section.classList.add('visible');
      features.forEach(f => f.classList.add('visible'));
      extras.forEach(el => el.classList.add('visible'));
    }
  }

  window.addEventListener('scroll', revealOnScroll);
  window.addEventListener('load', revealOnScroll);

 document.getElementById("scrollTopLink").addEventListener("click", function(e) {
        e.preventDefault(); // Prevent jumping/reloading
        document.getElementById("home").scrollIntoView({ behavior: "smooth" });
    });
