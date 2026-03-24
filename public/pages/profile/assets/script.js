function copyId() {
	const idSpan = document.getElementById('copy-id');
	const id = idSpan ? idSpan.textContent.trim() : false;
	if (!id) return;
	navigator.clipboard.writeText(id);
	const btn = document.getElementById('copy-id-btn');
	btn.classList.add('copied');
	btn.innerHTML = '<i class="fa fa-check mr-2 text-green-500"></i><font class="text-green-500">Скопировано!</font>';
	setTimeout(() => {
		btn.classList.remove('copied');
		btn.innerHTML = '<i class="fa fa-clone mr-2 text-gray-400"></i>';
	}, 10000);
}

function copyLink() {
	const input = document.getElementById('subscription-link');
	const link = input ? input.value : '';
	if (!link) return;
	navigator.clipboard.writeText(link);
	const btn = document.getElementById('copy-link-btn');
	btn.innerHTML = '<i class="fa fa-check text-green-500"></i>';
	setTimeout(() => {
		btn.innerHTML = '<i class="fa fa-clone text-gray-400"></i>';
	}, 10000);
}

const modal = document.getElementById('modal');
const closeBtn = document.getElementById('close-modal');

// Открытие модального окна
function openModal(ev) {
	const titleElem = ev.querySelector('.modal-title');
	const contentElem = ev.querySelector('.modal-content');
	const modalTitle = document.getElementById('modal-title');
	const modalContent = document.getElementById('modal-content');
	modalTitle.innerText = titleElem ? titleElem.innerText : '';
	modalContent.innerHTML = contentElem ? contentElem.innerHTML : '';
	modal.classList.remove('modal-hidden');
}

// Закрытие по кнопке
closeBtn.onclick = function (e) {
	e.stopPropagation();
	modal.classList.add('modal-hidden');
};

// Закрытие по клику вне окна
modal.onclick = function (e) {
	if (e.target === modal) {
		modal.classList.add('modal-hidden');
	}
};