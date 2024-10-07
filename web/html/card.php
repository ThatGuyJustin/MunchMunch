<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kraft Mac and Cheese Recipe</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 20px; /* Rounder edges for the container */
        }
        .title-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .title-section h1 {
            font-size: 32px;
            margin: 0;
        }
        .title-section .author {
            font-size: 18px;
            color: gray;
        }
        .main-content {
            display: flex;
            margin-top: 20px;
        }
        .left-panel {
            width: 50%;
        }
        .right-panel {
            width: 50%;
            text-align: right;
        }
        .right-panel img {
            width: 100%;
            border-radius: 20px; /* Rounder edges for the image */
        }
        .recipe-details {
            font-size: 18px;
            margin-bottom: 10px;
        }
        .buttons {
            margin-top: 20px;
        }
        .buttons button {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            margin-right: 10px;
            border-radius: 12px; /* Rounder edges for the buttons */
        }
        .buttons button:hover {
            background-color: #218838;
        }
        .review-section {
            margin-top: 40px;
        }
        .review-section h2 {
            font-size: 24px;
            margin-bottom: 10px;
        }
        .review-section textarea {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 12px; /* Rounder edges for the review textarea */
            height: 100px;
        }
    </style>
</head>
<body>

<div class="container">
    <!-- Title and Author Section -->
    <div class="title-section">
        <h1>Kraft Mac and Cheese</h1>
        <span class="author">By Some Random Person (600)</span>
    </div>

    <!-- Main Content Section -->
    <div class="main-content">
        <!-- Left Panel with Recipe Details -->
        <div class="left-panel">
            <div class="recipe-details">Tags: Pasta, American</div>
            <div class="recipe-details">Time to Cook: 10 Minutes</div>
            <div class="recipe-details">Skill Level: Beginner</div>
            <div class="recipe-details">Menu Label 1</div>
            <div class="recipe-details">Menu Label 2</div>
            <div class="buttons">
                <button>Cook!</button>
                <button>Save for later</button>
            </div>
        </div>

        <!-- Right Panel with Recipe Image -->
        <div class="right-panel">
            <img src="html/pictures/recipe/macandcheese.jpg" alt="Kraft Mac and Cheese">
        </div>
    </div>

    <!-- Review Section -->
    <div class="review-section">
        <h2>Review</h2>
        <textarea placeholder="Write your review here..."></textarea>
    </div>
</div>

</body>
</html>
