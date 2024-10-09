// Check if the script is loaded
console.log("Script is loaded!");

// Function to add an ingredient to the list
function addIngredient() {
    console.log("Add Ingredient button clicked!"); // Check if the function is being triggered

    const ingredientName = document.getElementById('ingredient-name').value;
    const quantity = document.getElementById('ingredient-quantity').value;
    const unit = document.getElementById('ingredient-unit').value;

    if (ingredientName && quantity && unit) {
        const combined = `${quantity} ${unit}`; // Combine quantity and unit

        const ingredientList = document.getElementById('ingredient-list');
        const ingredientItem = document.createElement('li');
        ingredientItem.textContent = `${combined} of ${ingredientName}`; // Display combined value

        // Hidden input to store the ingredient as JSON
        const ingredientData = document.createElement('input');
        ingredientData.type = 'hidden';
        ingredientData.name = 'ingredients[]';
        ingredientData.value = JSON.stringify({ [ingredientName]: combined });

        ingredientItem.appendChild(ingredientData);
        ingredientList.appendChild(ingredientItem);

        // Clear the input fields
        document.getElementById('ingredient-name').value = '';
        document.getElementById('ingredient-quantity').value = '';
        document.getElementById('ingredient-unit').selectedIndex = 0;
    } else {
        alert("Please fill out all ingredient fields.");
    }
}
