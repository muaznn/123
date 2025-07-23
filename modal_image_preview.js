document.addEventListener('DOMContentLoaded', () => {
  // Create modal elements
  const modalOverlay = document.createElement('div');
  modalOverlay.id = 'imageModalOverlay';
  modalOverlay.style.position = 'fixed';
  modalOverlay.style.top = '0';
  modalOverlay.style.left = '0';
  modalOverlay.style.width = '100vw';
  modalOverlay.style.height = '100vh';
  modalOverlay.style.backgroundColor = 'rgba(0, 0, 0, 0.7)';
  modalOverlay.style.display = 'none';
  modalOverlay.style.justifyContent = 'center';
  modalOverlay.style.alignItems = 'center';
  modalOverlay.style.zIndex = '100000';

  const modalImage = document.createElement('img');
  modalImage.id = 'modalImage';
  modalImage.style.maxWidth = '90%';
  modalImage.style.maxHeight = '90%';
  modalImage.style.borderRadius = '15px';
  modalImage.style.boxShadow = '0 4px 12px rgba(0,0,0,0.5)';
  modalImage.style.backgroundColor = 'white'; // Added white background
  modalOverlay.appendChild(modalImage);

  document.body.appendChild(modalOverlay);

  // Function to open modal with image src
  function openModal(src) {
    modalImage.src = src;
    modalOverlay.style.display = 'flex';
  }

  // Close modal on overlay click
  modalOverlay.addEventListener('click', () => {
    modalOverlay.style.display = 'none';
    modalImage.src = '';
  });

  // Attach click event to main image and additional images
  const mainImg = document.getElementById('equipment-img');
  if (mainImg) {
    mainImg.style.cursor = 'pointer';
    mainImg.addEventListener('click', () => {
      openModal(mainImg.src);
    });
  }

  const additionalThumbnails = document.getElementById('additional-thumbnails');
  if (additionalThumbnails) {
    additionalThumbnails.addEventListener('click', (event) => {
      if (event.target && event.target.tagName === 'IMG') {
        openModal(event.target.src);
      }
    });
  }
});
