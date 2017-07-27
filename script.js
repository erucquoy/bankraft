function playervserver(id) {
	var item1 = document.getElementById('playervserver1');
	var item2 = document.getElementById('playervserver2');
	if(id == 1) {
		item1.style.backgroundColor = "#2D6AA8";
		item1.style.color = "#FFFFFF";
		item2.style.background = "none";
		item2.style.color = "#333333";
		document.getElementById('signupsubmit').value = "Sign up !";
		document.getElementById('usertype').value = 1;
	}
	else  {
		item2.style.backgroundColor = "#2D6AA8";
		item2.style.color = "#FFFFFF";
		item1.style.background = "none";
		item1.style.color = "#333333";
		document.getElementById('signupsubmit').value = "Sign up & download plugin !";
		document.getElementById('usertype').value = 2;
	}
}

function init() {
	document.getElementById('pricetokens').addEventListener('change', change_itemname, false);
}

document.addEventListener('DOMContentLoaded', init, false);
