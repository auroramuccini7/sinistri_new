<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<title>Gestione Sinistri - Menu</title>
<style>
    body {
        font-family: Arial, sans-serif;
        background: #f4f6f8;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        height: 100vh;
        margin: 0;
    }
    h1 {
        color: #1a73e8;
        margin-bottom: 40px;
    }
    .menu {
        display: grid;
        grid-template-columns: repeat(2, 220px);
        grid-gap: 25px;
    }
    .btn {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-decoration: none;
        font-size: 20px;
        font-weight: bold;
        color: white;
        background: #1a73e8;
        padding: 40px 20px;
        border-radius: 15px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        transition: transform 0.2s, background 0.3s;
        text-align: center;
    }
    .btn:hover {
        transform: translateY(-4px);
        background: #155ab6;
    }
    .emoji {
        font-size: 40px;
        margin-bottom: 10px;
    }
</style>
</head>
<body>

<h1>Gestione Sinistri</h1>
<div class="menu">
    <a href="aggiungi.php" class="btn">
        <div class="emoji">➕</div>
        Aggiungi Sinistro
    </a>
    <!-- <a href="ricerca_sinistri.php" class="btn">
        <div class="emoji">🔎</div>
        Ricerca Sinistri
    </a> -->
    <a href="visualizza_sinistri.php" class="btn">
        <div class="emoji">📊</div>
        Visualizza Tutti
    </a>
    <a href="export_scelta.php" class="btn">
        <div class="emoji">📥</div>
        Esporta Excel
    </a>
</div>

</body>
</html>
