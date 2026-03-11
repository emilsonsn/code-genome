const tutorialOverlay = document.getElementById('tutorial-overlay')

function hideTutorial() {
    tutorialOverlay.classList.add('fade-out')
    setTimeout(() => {
        tutorialOverlay.style.display = 'none'
        tutorialOverlay.style.pointerEvents = 'none'
    }, 500)
}

// Hide tutorial on click and prevent propagation to scene behind
tutorialOverlay.addEventListener('click', (event) => {
    event.stopPropagation()
    hideTutorial()
})

// Prevent all pointer events from propagating to the scene
tutorialOverlay.addEventListener('pointerdown', (event) => event.stopPropagation())
tutorialOverlay.addEventListener('pointerup', (event) => event.stopPropagation())
tutorialOverlay.addEventListener('pointermove', (event) => event.stopPropagation())

// Auto-hide tutorial after 4 seconds
setTimeout(hideTutorial, 4000)
