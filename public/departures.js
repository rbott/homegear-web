function getDeparturesData() {
	const myRequest = new Request('/transportation');

	fetch(myRequest)
		.then(response => response.json())
		.then(data => {
			for (const element of data.elements) {
				console.log(element)
			}
		})
		.catch(console.error);
}
