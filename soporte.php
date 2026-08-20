<?php include("includes/header.php"); ?>


<section class="support-section">
    <div class="form-wrapper">
        <h2>¿En qué podemos ayudarte?</h2>
        <p>Escribe tu consulta y te responderemos lo antes posible.</p>

        <form action="" class="support-form">
            <div class="form-group">
                <label for="name">Nombre completo</label>
                <input type="text" id="name" placeholder="Tu nombre" required>
            </div>
            <div class="form-group">
                <label for="email">Correo electrónico</label>
                <input type="email" id="email" placeholder="[EMAIL_ADDRESS]" required>
            </div>
            <div class="form-group">
                <label for="message">Mensaje</label>
                <textarea id="message" rows="5" placeholder="Describe tu consulta..." required></textarea>
            </div>
            <button type="submit" class="btn-submit">Enviar Mensaje</button>
        </form>

        <div class="contact-info">
            <h3>También puedes contactarnos por:</h3>
            <p><strong>Whatsapp:</strong> +598 123012415</p>
            <p><strong>Correo:</strong> [EMAIL_ADDRESS]</p>
            <div class="social-links">
                <a href="#"><i class="fab fa-whatsapp"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-facebook-f"></i></a>
            </div>
        </div>
    </div>
</section>



<?php include("includes/footer.php"); ?>