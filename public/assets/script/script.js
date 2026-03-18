const card = document.getElementById('spiner-card');

setTimeout(() => {
	card.style.transition = '1s ease-in-out all'
	card.style.opacity = '0';
	setTimeout(() => {
		card.style.display = 'none';
	}, 2000);
}, 2200);

const images = document.querySelectorAll('.slide-in-bottom');

images.forEach((img, index) => {
  img.style.animationDelay = `${index * 0.15}s`;
});