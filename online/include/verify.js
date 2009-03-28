function validatePassword(password) {
	if (password.length < 4) { alert('Password must be at least 4 characters long'); return 0;
	}
	else { return 1; }
}
