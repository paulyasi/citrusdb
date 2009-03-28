function validatePassword(password) {
	if (password.length < 6) { alert('Password must be at least 6 characters long'); return 0;
	}
	else { return 1; }
}
