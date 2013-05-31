function validatePassword(password) {
	if (password.length < 7) { alert('Password must be at least 7 characters long'); return 0;
	}
	else { return 1; }
}
