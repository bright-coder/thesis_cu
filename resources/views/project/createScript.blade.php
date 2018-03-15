<script type="text/javascript">
$(".form-text.text-muted").hide();

$("#save").submit(function (event) {
    //event.preventDefault();
    $.post("/project", { name: "John", time: "2pm" });
    //console.log("save firer");
    //$(this).hide();
    
});
</script>
