document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('particles-container');
    if (!container) return;

    const colors = ['color-indigo', 'color-emerald', 'color-purple', 'color-cyan', 'color-pink'];
    const codeSymbols = ['{ }', '< />', 'fn()', '[ ]', '0x', '//'];
    const particleTypes = ['particle-dot', 'particle-gene', 'particle-helix', 'particle-code'];

    function createParticle() {
        const particle = document.createElement('div');
        const type = particleTypes[Math.floor(Math.random() * particleTypes.length)];
        const color = colors[Math.floor(Math.random() * colors.length)];
        
        particle.classList.add('particle', type, color);
        
        // Random position
        particle.style.left = Math.random() * 100 + '%';
        
        // Random duration between 6-12 seconds (faster)
        const duration = 6 + Math.random() * 6;
        particle.style.animationDuration = duration + 's';
        
        // Random delay (shorter)
        particle.style.animationDelay = Math.random() * 2 + 's';

        // Code symbols for code particles
        if (type === 'particle-code') {
            particle.textContent = codeSymbols[Math.floor(Math.random() * codeSymbols.length)];
        }

        container.appendChild(particle);

        // Remove particle after animation
        setTimeout(() => {
            particle.remove();
        }, (duration + 2) * 1000);
    }

    // Create initial particles
    for (let i = 0; i < 25; i++) {
        setTimeout(createParticle, i * 100);
    }

    // Continuously create new particles (faster)
    setInterval(createParticle, 400);
});
