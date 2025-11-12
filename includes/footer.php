<footer class="site-footer">
  <div class="container footer-grid">
    <div class="footer-brand">
      <img src="/KEMELS_GALE/assets/images/eoliennes/logo2.png" alt="KEMEL’S GALE" />
      <p>KEMEL’S GALE — Wind Farm<br><small>Énergie propre, durable et locale.</small></p>
    </div>

    <nav class="footer-nav">
      <h4>Navigation</h4>
      <ul>
        <li><a href="/KEMELS_GALE/index.php">Accueil</a></li>
        <li><a href="/KEMELS_GALE/description.php">Description</a></li>
        <li><a href="/KEMELS_GALE/gerants.php">Gérants</a></li>
        <li><a href="/KEMELS_GALE/carte.php">Carte</a></li>
        <li><a href="/KEMELS_GALE/contact.php">Contact</a></li>
      </ul>
    </nav>

    <div class="footer-contact">
      <h4>Contact</h4>
      <ul>
        <li><a href="mailto:contact@kemelsgale.fr">contact@kemelsgale.fr</a></li>
        <li><a href="#">Mentions légales</a></li>
        <li><a href="#">Confidentialité</a></li>
      </ul>
    </div>
  </div>

  <div class="footer-legal">
    <small>© <span id="year"></span> KEMEL’S GALE. Tous droits réservés.</small>
  </div>
</footer>

<script defer src="/KEMELS_GALE/assets/js/principal.js"></script>
<script>
  // année automatique si ton principal.js ne le fait pas
  document.getElementById('year').textContent = new Date().getFullYear();
</script>

</body>
</html>
