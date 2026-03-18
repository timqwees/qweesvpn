document.addEventListener('DOMContentLoaded', () => {

	function selectPlan(el) {
		document.querySelectorAll('.plan-card').forEach(card => card.classList.remove('selected'));
		el.classList.add('selected');
		document.getElementById('selected-plan').value = el.getAttribute('data-plan');
	}

	function selectPayment(el) {
		document.querySelectorAll('.payment-method').forEach(m => m.classList.remove('selected'));
		el.classList.add('selected');
		document.getElementById('selected-method').value = el.getAttribute('data-method');
	}

});