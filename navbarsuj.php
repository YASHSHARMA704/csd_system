<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>navbar</title>
    <style>
    .header {
        background-color: #002147;
        color: white;
        padding: 15px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap; /* Allows items to wrap to the next line */
    }
    .header h1 {
        color: white;
        font-size: 1.5rem; /* Responsive font size */
        text-align: center; /* Center the text */
        margin: 10px 0; /* Margin for spacing */
        /* flex: 1 1 100%; Ensures full width on small screens */
    }
    .header img {
        height: 70px;
        max-width: 100%; /* Prevents images from exceeding container width */
        flex: 0 0 auto; /* Prevents shrinking */
        margin: 10px; /* Margin for spacing */
    } 

    

    /* Media Queries */
    @media (max-width: 600px) {
        .header {
        flex-direction: column; /* Stack items vertically on small screens */
        align-items: center; /* Center items vertically */
        }
        .header h1 {
        font-size: 1.2rem; /* Smaller font size for smaller screens */
        }
    }

    @media (min-width: 601px) and (max-width: 1200px) {
        .header h1 {
        font-size: 1.3rem; /* Adjust font size for medium screens */
        }
    }

    /* Example for very large screens */
    @media (min-width: 1201px) {
        .header h1 {
        font-size: 1.5rem; /* Default font size for very large screens */
        }
    }
    </style>

</head>
<body>
  <div class="header">
    <img src="./images/drdo_logo_1.png" alt="DRDO Logo" />
    <h1>Centre for Fire, Explosive and Environment Safety (CFEES) <br>
            अग्नि, विस्फोटक और पर्यावरण सुरक्षा केंद्र (सीएफईईएस)
        </h1>
    <img src="./images/drdo_logo_1.png" alt="DRDO Logo" />
  </div>
  
</body>
</html>