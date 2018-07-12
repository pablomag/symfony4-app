function likePost(path, id) {
	likeUnlike
	(
		document.getElementById('like-' + id),
		document.getElementById('unlike-' + id),
		document.getElementById('unlike-badge-' + id),
		path
	);
}

function unlikePost(path, id) {
	likeUnlike
	(
		document.getElementById('unlike-' + id),
		document.getElementById('like-' + id),
		document.getElementById('like-badge-' + id),
		path
	);
}

function switchButtons(button, oppositeButton) {
	button.disabled = false;
	button.style.display = 'none';
	oppositeButton.style.display = 'block';
}

function likeUnlike(button, oppositeButton, likeCount, path) {
	button.disabled = true;

	fetch(path, {'credentials': 'include'}).then(function (response) {
		response.json().then(function (json) {
			likeCount.innerText = json.count;
			switchButtons(button, oppositeButton);
		});
	}).catch(function () {
		switchButtons(button, oppositeButton);
	});

	event.preventDefault();
}
