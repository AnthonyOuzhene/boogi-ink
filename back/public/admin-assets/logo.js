// Logo du backoffice : le contour se dessine (tracé d'aiguille, voir admin.css) à la PREMIÈRE
// page de la session seulement. Chaque clic du backoffice recharge la page : sans ce garde-fou,
// l'animation se rejouerait sans arrêt.
// sessionStorage : oublié à la fermeture de l'onglet ; try/catch : navigation privée stricte.
(function () {
    try {
        if (sessionStorage.getItem('boLogoTraced')) {
            return;
        }
        sessionStorage.setItem('boLogoTraced', '1');
    } catch (e) {
        return; // stockage indisponible : pas d'animation plutôt qu'une animation à chaque page
    }
    document.documentElement.classList.add('bo-logo-trace');
})();
