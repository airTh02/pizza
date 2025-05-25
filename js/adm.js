function adm_abrir() {
    const senha = prompt("Informe o código do Administrador:");
    if (senha === "2222") {
        window.location.href = "http://localhost/template_pizza/adm.php";
    } else {
        alert("Código incorreto!");
    }
}