// Gallery Configuration
const galleryConfig = {
    images: [
        {
            id: 1,
            src: 'img/gal1.jpeg',
            title: 'E-Commerce Dashboard',
            description: 'Modern dashboard design for e-commerce analytics with dark theme',
            category: 'UI/UX Design'
        },
        {
            id: 2,
            src: 'img/gal2.jpeg',
            title: 'Mobile Banking App',
            description: 'User-friendly banking application with secure transaction features',
            category: 'Mobile Design'
        },
        {
            id: 3,
            src: 'img/gal3.jpeg',
            title: 'Travel Booking Platform',
            description: 'Intuitive travel booking interface with real-time availability',
            category: 'Web Design'
        },
        {
            id: 4,
            src: 'img/gal4.jpeg',
            title: 'Fitness Tracker Dashboard',
            description: 'Health and fitness tracking dashboard with progress visualization',
            category: 'UI/UX Design'
        }
    ],
    
    // Default image if main images are missing
    defaultImage: {
        id: 0,
        src: 'https://via.placeholder.com/900x550/00ADB5/ffffff?text=Gallery+Image',
        title: 'Sample Design Project',
        description: 'This is a sample project description. Add your own images to the gallery.',
        category: 'Design'
    },
    
    // Additional images for demonstration (can be unlimited)
    demoImages: [
        {
            id: 5,
            src: 'img/gal5.jpeg',
            title: 'Social Media Dashboard',
            description: 'Comprehensive social media analytics and management dashboard',
            category: 'Dashboard Design'
        },
        {
            id: 6,
            src: 'img/gal6.jpeg',
            title: 'Food Delivery App',
            description: 'Seamless food ordering experience with real-time tracking',
            category: 'Mobile App'
        },
        {
            id: 7,
            src: 'img/gal7.jpeg',
            title: 'Portfolio Website',
            description: 'Creative portfolio design for designers and artists',
            category: 'Web Design'
        },
        {
            id: 8,
            src: 'img/gal8.jpeg',
            title: 'Weather Application',
            description: 'Beautiful weather app with animated forecasts',
            category: 'Mobile Design'
        }
    ]
};

// Gallery State
let currentIndex = 0;
let images = [...galleryConfig.images];

// DOM Elements
const puzzleContainer = document.getElementById('puzzleContainer');
const imageTitle = document.querySelector('.image-title');
const imageDescription = document.querySelector('.image-description');
const projectCategory = document.querySelector('.project-category');
const thumbnailsGallery = document.getElementById('thumbnailsGallery');
const prevBtn = document.getElementById('prevBtn');
const nextBtn = document.getElementById('nextBtn');
const currentSlideSpan = document.querySelector('.current-slide');
const totalSlidesSpan = document.querySelector('.total-slides');
const imageCountSpan = document.getElementById('imageCount');
const addImageBtn = document.getElementById('addImageBtn');

// Initialize Gallery
function initGallery() {
    updateTotalSlides();
    createPuzzlePieces();
    createThumbnails();
    updateGallery();
    setupEventListeners();
    
    // Start with puzzle animation
    setTimeout(() => {
        animatePuzzle('assemble');
    }, 500);
}

// Create Puzzle Pieces
function createPuzzlePieces() {
    puzzleContainer.innerHTML = '';
    
    // Create 9 puzzle pieces (3x3 grid)
    for (let i = 0; i < 9; i++) {
        const piece = document.createElement('div');
        piece.className = 'puzzle-piece';
        piece.dataset.index = i;
        puzzleContainer.appendChild(piece);
    }
    
    updatePuzzleBackground();
}

// Update Puzzle Background
function updatePuzzleBackground() {
    const currentImage = images[currentIndex];
    const puzzlePieces = document.querySelectorAll('.puzzle-piece');
    
    puzzlePieces.forEach(piece => {
        piece.style.backgroundImage = `url('${currentImage.src}')`;
        
        // Calculate background position for each piece
        const index = parseInt(piece.dataset.index);
        const row = Math.floor(index / 3);
        const col = index % 3;
        
        const xPos = col * (100/3);
        const yPos = row * (100/3);
        
        piece.style.backgroundPosition = `${xPos}% ${yPos}%`;
        piece.style.backgroundSize = '300% 300%';
    });
}

// Animate Puzzle
function animatePuzzle(type = 'assemble') {
    const puzzlePieces = document.querySelectorAll('.puzzle-piece');
    
    puzzlePieces.forEach((piece, index) => {
        // Reset animation
        piece.style.animation = 'none';
        
        // Calculate delay for cascading effect
        const delay = index * 0.05;
        
        // Apply new animation
        setTimeout(() => {
            if (type === 'assemble') {
                piece.style.animation = `puzzleAssemble 0.8s ease-out forwards`;
            } else {
                piece.style.animation = `puzzleDisassemble 0.6s ease-out forwards`;
            }
        }, delay * 1000);
        
        // Reset animation after completion
        setTimeout(() => {
            piece.style.animation = '';
        }, 1000);
    });
}

// Create Thumbnails
function createThumbnails() {
    thumbnailsGallery.innerHTML = '';
    
    images.forEach((image, index) => {
        const thumbnail = document.createElement('div');
        thumbnail.className = `thumbnail-item ${index === currentIndex ? 'active' : ''}`;
        thumbnail.dataset.index = index;
        
        thumbnail.innerHTML = `
            <img src="${image.src}" alt="${image.title}" loading="lazy">
            <div class="thumbnail-overlay">
                <div class="thumbnail-title">${image.title}</div>
            </div>
        `;
        
        thumbnail.addEventListener('click', () => {
            changeImage(index);
        });
        
        thumbnailsGallery.appendChild(thumbnail);
    });
}

// Change Current Image
function changeImage(newIndex) {
    if (newIndex === currentIndex) return;
    
    // Animate puzzle disassembly
    animatePuzzle('disassemble');
    
    // Update current index
    currentIndex = newIndex;
    
    // Update gallery after animation
    setTimeout(() => {
        updateGallery();
        animatePuzzle('assemble');
    }, 600);
}

// Update Gallery Display
function updateGallery() {
    const currentImage = images[currentIndex];
    
    // Update puzzle background
    updatePuzzleBackground();
    
    // Update image info
    imageTitle.textContent = currentImage.title;
    imageDescription.textContent = currentImage.description;
    projectCategory.textContent = currentImage.category;
    
    // Update slide indicator
    currentSlideSpan.textContent = currentIndex + 1;
    
    // Update active thumbnail
    document.querySelectorAll('.thumbnail-item').forEach((thumb, index) => {
        thumb.classList.toggle('active', index === currentIndex);
    });
    
    // Update button states
    prevBtn.disabled = currentIndex === 0;
    nextBtn.disabled = currentIndex === images.length - 1;
    
    // Add pulse animation to active thumbnail
    const activeThumb = document.querySelector('.thumbnail-item.active');
    activeThumb.classList.add('pulse');
    setTimeout(() => activeThumb.classList.remove('pulse'), 2000);
}

// Update Total Slides Display
function updateTotalSlides() {
    totalSlidesSpan.textContent = images.length;
    imageCountSpan.textContent = images.length;
}

// Add New Image (Demonstration)
function addNewImage() {
    if (images.length >= 10) {
        alert('Maximum 10 images for demonstration. In real use, you can add unlimited images!');
        return;
    }
    
    // Get random demo image
    const demoImages = galleryConfig.demoImages.filter(img => 
        !images.some(existingImg => existingImg.id === img.id)
    );
    
    if (demoImages.length === 0) {
        alert('All demo images have been added! Add your own images to the gallery.');
        return;
    }
    
    const randomImage = demoImages[Math.floor(Math.random() * demoImages.length)];
    images.push(randomImage);
    
    // Update gallery
    updateTotalSlides();
    createThumbnails();
    updateGallery();
    
    // Show feedback
    addImageBtn.textContent = `+ Added: ${randomImage.title}`;
    setTimeout(() => {
        addImageBtn.textContent = '+ Add New Image to Gallery';
    }, 1500);
}

// Setup Event Listeners
function setupEventListeners() {
    // Navigation buttons
    prevBtn.addEventListener('click', () => {
        if (currentIndex > 0) {
            changeImage(currentIndex - 1);
        }
    });
    
    nextBtn.addEventListener('click', () => {
        if (currentIndex < images.length - 1) {
            changeImage(currentIndex + 1);
        }
    });
    
    // Add image button
    addImageBtn.addEventListener('click', addNewImage);
    
    // Keyboard navigation
    document.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowLeft') {
            if (currentIndex > 0) changeImage(currentIndex - 1);
        } else if (e.key === 'ArrowRight') {
            if (currentIndex < images.length - 1) changeImage(currentIndex + 1);
        }
    });
    
    // Auto-play (optional)
    let autoPlayInterval;
    
    function startAutoPlay() {
        autoPlayInterval = setInterval(() => {
            const nextIndex = (currentIndex + 1) % images.length;
            changeImage(nextIndex);
        }, 5000);
    }
    
    function stopAutoPlay() {
        clearInterval(autoPlayInterval);
    }
    
    // Start auto-play on mouse leave, stop on mouse enter
    const gallery = document.querySelector('.gallery-container');
    gallery.addEventListener('mouseenter', stopAutoPlay);
    gallery.addEventListener('mouseleave', startAutoPlay);
    
    // Start auto-play initially
    startAutoPlay();
}

// Handle missing images
function handleMissingImages() {
    images = images.map(image => {
        // Check if image exists (in real implementation, you'd do a proper check)
        // For demo, we'll just use the default if image path seems invalid
        if (!image.src || image.src.includes('gal') && !image.src.includes('.jpeg')) {
            return { ...galleryConfig.defaultImage, id: image.id };
        }
        return image;
    });
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    handleMissingImages();
    initGallery();
});

// Export for testing
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { galleryConfig, changeImage, addNewImage };
}