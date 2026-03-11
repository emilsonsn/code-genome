const tutorialOverlay = document.getElementById('tutorial-overlay')

function hideTutorial() {
    tutorialOverlay.classList.add('fade-out')
    setTimeout(() => {
        tutorialOverlay.style.display = 'none'
        tutorialOverlay.style.pointerEvents = 'none'
    }, 500)
}

tutorialOverlay.addEventListener('click', (event) => {
    event.stopPropagation()
    hideTutorial()
})

tutorialOverlay.addEventListener('pointerdown', (event) => event.stopPropagation())
tutorialOverlay.addEventListener('pointerup', (event) => event.stopPropagation())
tutorialOverlay.addEventListener('pointermove', (event) => event.stopPropagation())

setTimeout(hideTutorial, 4000)
