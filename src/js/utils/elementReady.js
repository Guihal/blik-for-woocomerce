export async function elementReady(selector, parent = false) {
	return new Promise((resolve) => {
		let block = document.querySelector(selector);

		if (block) {
			resolve(block);
			return;
		}

		const observer = new MutationObserver((mutations, obs) => {
			block = parent ? parent.querySelector(selector) : document.querySelector(selector);

			if (block) {
				resolve(block);
				obs.disconnect();
			}
		});

		observer.observe(parent ? parent : document.documentElement, { childList: true, subtree: true });
	});
}
