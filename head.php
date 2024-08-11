<head>
  <meta name="viewport" content="width=device-width, initial-scale=1">
</head>

<body>
<style>
  body {
    font-family: sans-serif, sans-serif; /* Replace with your chosen font */
    color: #333; /* Adjust base text color */
    background-color: #f8f8f8; /* Add a light background */
    padding: 1rem;
    display: flex;
    flex-direction: column;
    gap: 2rem;
  }

  body > * {
    margin: 0 auto;
    max-width: 1024px;
    width: 100%;
  }

  h2 {
    margin: 0 0 1rem 0;
    line-height: 1;
  }

  summary {
    display: none;
  }

table, .table {
  border-collapse: collapse;
  width: 100%;
  border: 1px solid #ddd; /* Add table border */
}

thead {
  position: sticky;
  top: 0;
  background: #f8f8f8;
}

th, td, .td {
  padding: 1rem;
  border: 1px solid #ddd;
}

form {
  display: grid; /* Use grid for better layout control */
  grid-template-columns: 1fr;
  gap: 2rem;
}

fieldset {
  border: none; /* Remove fieldset border */
  padding: 0;
  border: 1px solid;
  padding: 2rem 1rem;
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
}

fieldset h2 {
  grid-column: span 2;
}

button {
  background-color: #3b3b3b;
  color: #fff;
  border: none;
  padding: 0.8rem 1.5rem;
  cursor: pointer;
}

body {
  /* ... */
  max-width: 90vw; /* Adjust maximum width for responsiveness */
}

a {
  color: inherit; /* Base link color (blue) */
  text-decoration: none;
}

a:hover {
  color: inherit; /* Darker shade on hover */
  text-decoration: underline;
}

/* Optional: for visited links */
a:visited {
  color: inherit; /* Different color for visited links */
}

[aria-current="page"] {
  background-color: lightgrey;
}

[aria-current="page"] a {
  text-decoration: underline;
}

label {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  line-height: 1;
}

.radio {
  display: grid;
  grid-template-colums: repeat(2, 1fr);
  max-height: 2rem;
}


.radio label {
  flex-direction: row;
  align-items: center;
}

.radio > span {
  grid-column: span 2;
}

.error {
  color: #e74c3c;
  font-size: 12px;
  display: block;
}

.error-button {
  color: #000;
  background: #e74c3c;
}

input {
  width: 100%;
}

/* Media query for smaller screens */
@media (max-width: 768px) {
  body {
    padding: 0.5rem; /* Reduce padding */
    font-size: 14px;
  }

  table {
    /* Adjust table layout for smaller screens */
    display: block;
    overflow-x: auto; /* Enable horizontal scrolling if needed */
  }

  th, td {
    /* Adjust cell padding and text alignment */
    padding: 0.5rem;
    text-align: left;
  }

  fieldset {
    grid-template-columns: 1fr; /* Make fields stack on smaller screens */
  }

  /* Other adjustments for smaller screens */
}


</style>