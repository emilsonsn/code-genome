import * as THREE from 'three'
import { OrbitControls } from 'three/examples/jsm/controls/OrbitControls.js'

const repositories = window.repositories ?? []
const MIN_DISTANCE = 18

const scene = new THREE.Scene()
const raycaster = new THREE.Raycaster()
const mouse = new THREE.Vector2()
const clock = new THREE.Clock()

const camera = new THREE.PerspectiveCamera(
    60,
    window.innerWidth / window.innerHeight,
    0.1,
    1000
)

camera.position.z = 60

const renderer = new THREE.WebGLRenderer({
    antialias: true,
    alpha: true
})

renderer.setSize(window.innerWidth, window.innerHeight)
renderer.setPixelRatio(window.devicePixelRatio)
document.body.appendChild(renderer.domElement)

const controls = new OrbitControls(camera, renderer.domElement)
controls.enableDamping = true
controls.dampingFactor = 0.05
controls.rotateSpeed = 0.8
controls.zoomSpeed = 1
controls.panSpeed = 0.8

const ambient = new THREE.AmbientLight(0xffffff, 1.2)
scene.add(ambient)

const light = new THREE.PointLight(0x60a5fa, 2, 200)
light.position.set(30, 30, 30)
scene.add(light)

const dnaObjects = []
const clickableMeshes = []

let pointerDown = false
let dragMoved = false
let startX = 0
let startY = 0

const labelContainer = document.getElementById('labels')
const repoCard = document.getElementById('repo-card')
const repoCardName = document.getElementById('repo-card-name')
const repoCardOwner = document.getElementById('repo-card-owner')
const repoCardScore = document.getElementById('repo-card-score')
const repoCardStars = document.getElementById('repo-card-stars')
const repoCardContributors = document.getElementById('repo-card-contributors')
const repoCardLink = document.getElementById('repo-card-link')
const repoCardClose = document.getElementById('repo-card-close')
const repoCardGrade = document.getElementById('repo-card-grade')
const repoCardSize = document.getElementById('repo-card-size')
const repoDocBar = document.getElementById('repo-doc-bar')
const repoTestBar = document.getElementById('repo-test-bar')
const repoStructureBar = document.getElementById('repo-structure-bar')
const repoMaintainBar = document.getElementById('repo-maintain-bar')

const gradeColors = {
    green: 0x22c55e,
    emerald: 0x10b981,
    yellow: 0xeab308,
    orange: 0xf97316,
    red: 0xef4444
}

function createDNA(score, color) {
    const group = new THREE.Group()

    const radius = 2 + score / 40
    const height = 10 + score / 10
    const points = 40
    const colorHex = gradeColors[color] ?? 0x38bdf8

    const sphereGeometry = new THREE.SphereGeometry(0.25, 16, 16)

    const material = new THREE.MeshStandardMaterial({
        color: colorHex,
        emissive: colorHex,
        emissiveIntensity: 0.35
    })

    for (let i = 0; i < points; i++) {
        const t = i / points
        const angle = t * Math.PI * 6
        const y = (t - 0.5) * height

        const left = new THREE.Mesh(sphereGeometry, material)
        left.position.set(
            Math.cos(angle) * radius,
            y,
            Math.sin(angle) * radius
        )

        const right = new THREE.Mesh(sphereGeometry, material)
        right.position.set(
            Math.cos(angle + Math.PI) * radius,
            y,
            Math.sin(angle + Math.PI) * radius
        )

        group.add(left)
        group.add(right)
    }

    const hitGeometry = new THREE.SphereGeometry(radius * 3, 32, 32)

    const hitMaterial = new THREE.MeshBasicMaterial({
        transparent: true,
        opacity: 0
    })

    const hitArea = new THREE.Mesh(hitGeometry, hitMaterial)

    group.add(hitArea)    

    return group
}

function generatePosition(existing) {
    let position
    let valid = false

    while (!valid) {
        position = new THREE.Vector3(
            (Math.random() - 0.5) * 80,
            (Math.random() - 0.5) * 40,
            (Math.random() - 0.5) * 40
        )

        valid = true

        for (const obj of existing) {
            const distance = position.distanceTo(obj.mesh.position)

            if (distance < MIN_DISTANCE) {
                valid = false
                break
            }
        }
    }

    return position
}

function createLabel(repo) {
    const label = document.createElement('div')

    label.className = 'repo-label'

    label.innerHTML = `
        <div class="repo-score">${repo.overall}</div>
        <div class="repo-name">${repo.repository_name}</div>
    `

    label.addEventListener('click', () => {
        showRepositoryCard(repo)
    })

    labelContainer.appendChild(label)

    return label
}

function updateLabels() {
    dnaObjects.forEach((obj) => {
        const vector = obj.mesh.position.clone()
        vector.project(camera)

        const x = (vector.x * 0.5 + 0.5) * window.innerWidth
        const y = (-vector.y * 0.5 + 0.5) * window.innerHeight

        obj.label.style.left = `${x}px`
        obj.label.style.top = `${y}px`
    })
}

function showRepositoryCard(repo) {

    repoCardName.innerText = repo.repository_name
    repoCardOwner.innerText = repo.owner

    repoCardScore.innerText = repo.overall
    repoCardGrade.innerText = repo.grade

    repoCardStars.innerText = repo.stars
    repoCardContributors.innerText = repo.contributors_count
    repoCardSize.innerText = repo.size

    repoCardLink.href = repo.url

    repoDocBar.style.width = repo.documentation + '%'
    repoTestBar.style.width = repo.tests + '%'
    repoStructureBar.style.width = repo.structure + '%'
    repoMaintainBar.style.width = repo.maintainability + '%'
    repoCardScore.style.color = ''
    repoCardScore.className = 'repo-score-big grade-' + repo.grade_color
    repoCard.classList.remove('hidden')
}

function hideRepositoryCard() {
    repoCard.classList.add('hidden')
}

function handlePointerDown(event) {
    pointerDown = true
    dragMoved = false
    startX = event.clientX
    startY = event.clientY
}

function handlePointerMove(event) {
    if (!pointerDown) {
        return
    }

    const deltaX = Math.abs(event.clientX - startX)
    const deltaY = Math.abs(event.clientY - startY)

    if (deltaX > 5 || deltaY > 5) {
        dragMoved = true
    }
}

function handlePointerUp(event) {
    if (!pointerDown) {
        return
    }

    pointerDown = false

    if (dragMoved) {
        return
    }

    mouse.x = (event.clientX / window.innerWidth) * 2 - 1
    mouse.y = -(event.clientY / window.innerHeight) * 2 + 1

    raycaster.setFromCamera(mouse, camera)

    const intersects = raycaster.intersectObjects(clickableMeshes, false)

    if (!intersects.length) {
        return
    }

    const repo = intersects[0].object.userData.repo

    if (repo) {
        showRepositoryCard(repo)
    }
}

function animate() {
    const elapsedTime = clock.getElapsedTime()

    dnaObjects.forEach((obj) => {
        obj.mesh.rotation.y += 0.003
        obj.mesh.rotation.x = Math.sin(elapsedTime * 0.5) * 0.2
    })

    controls.update()
    updateLabels()
    renderer.render(scene, camera)

    requestAnimationFrame(animate)
}

repositories.forEach((repo) => {
    const dna = createDNA(repo.overall, repo.grade_color)
    const position = generatePosition(dnaObjects)
    const label = createLabel(repo)

    dna.position.copy(position)
    dna.userData.repo = repo

    dna.traverse((child) => {
        if (child.isMesh) {
            child.userData.repo = repo
            clickableMeshes.push(child)
        }
    })

    scene.add(dna)

    dnaObjects.push({
        mesh: dna,
        label
    })
})

repoCardClose.addEventListener('click', hideRepositoryCard)

window.addEventListener('pointerdown', handlePointerDown)
window.addEventListener('pointermove', handlePointerMove)
window.addEventListener('pointerup', handlePointerUp)

window.addEventListener('resize', () => {
    camera.aspect = window.innerWidth / window.innerHeight
    camera.updateProjectionMatrix()
    renderer.setSize(window.innerWidth, window.innerHeight)
})

animate()