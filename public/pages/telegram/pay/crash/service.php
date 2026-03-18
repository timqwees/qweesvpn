<?php
use App\Config\Session;
use Setting\Route\Function\Functions;

// Инициализация сессии
Session::init();
$client = (new Functions())->client($_SESSION['client'] ?? 0);
?>
<div
	class="relative flex flex-col items-center justify-center min-h-screen from-slate-800 to-slate-900 font-sans bg-[#040B20]">

	<div class="container px-4 text-center">
		<h1 class="header-title text-2xl font-bold mb-4">Нельзя оформить подписку</h1>
		<?php if ($client['vpn_freekey'] === 'buy'): ?>
			<p class="header-desc text-gray-300 mb-6">
				У вас <a class="underline text-red-400 font-bold" href="/profile">уже активна платная подписка.</a>
				Новую
				оформить нельзя, пока не закончится текущая или не отмените её.
			</p>
		<?php else: ?>
			<p class="header-desc text-gray-300 mb-6">
				У вас <a class="underline text-red-400 font-bold" href="/profile">активна бесплатная подписка.</a> Чтобы
				приобрести платный тариф, сначала отмените бесплатный доступ.
			</p>
		<?php endif; ?>
		<button
			class="flex items-center justify-center gap-2 w-full px-4 py-2 mt-2 mb-4 rounded-xl bg-gradient-to-r from-red-500 via-pink-500 to-purple-500 hover:from-red-600 hover:via-pink-600 hover:to-purple-600 shadow-lg transition-all duration-200 border-2 border-red-300 hover:border-red-400 text-white font-medium text-base tracking-wide relative overflow-hidden group"
			command="show-modal" commandfor="delete_key">
			<span
				class="absolute left-0 top-0 h-full w-1 bg-red-400 rounded-l-xl group-hover:bg-white transition-all duration-300"></span>
			<i class="fa fa-exclamation-circle text-lg text-white drop-shadow-glow animate-pulse"></i>
			<?php if ($client['vpn_freekey'] === 'buy'): ?>
				<span class="ml-2">Отменить платную подписку</span>
			<?php else: ?>
				<span class="ml-2">Отменить бесплатную подписку</span>
			<?php endif; ?>
		</button>
		<a href="/profile" class="back-btn inline-block w-full mx-auto mb-3">
			Перейти в профиль
		</a>
		<a href="/" class="back-btn flex justify-center items-center gap-2 text-gray-400 hover:text-white">
			<i class="fa fas fa-door-open text-lg"></i>
			Вернуться на главную
		</a>
	</div>

	<dialog id="delete_key" class="w-full h-full bg-black/80 backdrop-blur-sm">
		<div class="flex justify-center items-center min-h-screen w-full">
			<div
				class="bg-gradient-to-b from-[#171c2f] to-[#181818] rounded-2xl shadow-2xl p-8 max-w-md w-full flex flex-col items-center gap-8 relative border border-[#262c44]/40">
				<button
					class="absolute top-4 right-4 text-gray-400 hover:text-white text-2xl focus:outline-none hover:scale-110 transition-transform"
					command="close" commandfor="delete_key" title="Закрыть" aria-label="Закрыть">
					&times;
				</button>
				<div class="flex flex-col items-center gap-3 w-full">
					<div class="flex items-center justify-center w-14 h-14 bg-red-500/10 rounded-full mb-2">
						<i class="fa fa-exclamation-triangle text-4xl text-red-400 animate-pulse"></i>
					</div>
					<h3 class="text-xl font-extrabold text-white text-center drop-shadow">Подтвердите удаление
						подписки</h3>
					<div class="text-gray-300 text-center text-base w-full leading-relaxed space-y-2">
						<?php if ($client['vpn_freekey'] === 'buy'): ?>
							<p>
								Вы действительно хотите <b class="text-red-400">отменить вашу платную
									VPN-подписку</b>?<br>
								<span class="text-gray-400">Это действие <span
										class="font-bold text-red-500">необратимо</span>.</span>
							</p>
							<p class="text-xs text-gray-400">
								После подтверждения <b>подписка будет немедленно отключена</b>.
							</p>
						<?php else: ?>
							<p>
								Вы действительно хотите отменить вашу <b class="text-red-400">бесплатную
									VPN-подписку</b>?<br>
								<span class="text-gray-400">Это действие <span
										class="font-bold text-red-500">необратимо</span>.</span>
							</p>
							<p class="text-xs text-gray-400">
								После подтверждения <b>бесплатная подписка будет немедленно отключена</b>.
							</p>
						<?php endif; ?>
					</div>
				</div>
				<div class="flex w-full gap-4 mt-3">
					<button
						class="flex-1 flex justify-center items-center gap-2 rounded-lg bg-[#232536] hover:bg-[#353750] text-gray-200 py-3 px-3 text-base font-medium shadow focus:outline-none transition"
						command="close" commandfor="delete_key" type="button">
						<i class="fa fa-arrow-left"></i>
						<span>Назад</span>
					</button>
					<a href="/delete_key/<?php echo htmlspecialchars($client['tg_id']); ?>" class=" flex-1 flex justify-center items-center gap-2 rounded-lg bg-gradient-to-r from-red-500 via-pink-500 to-purple-500 
							hover:from-red-600 hover:via-pink-600 hover:to-purple-600 text-white py-3 px-3 text-sm font-semibold shadow-xl 
							transition relative overflow-hidden focus:outline-none">
						<i class="fa fa-trash"></i>
						<span>Удалить</span>
					</a>
				</div>
			</div>
		</div>
	</dialog>

</div>