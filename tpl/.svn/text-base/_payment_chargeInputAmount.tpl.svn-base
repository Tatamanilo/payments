<form action="{$next_step_url}" id="form_charge" method="post">
    <input type="text" class="number" name="charge_amount" />
    <br />
    <input type="submit" value="Charge" />
</form>

<script>
{literal}
    $(document).ready(function(){
        $("#form_charge").validate( 
        {
            submitHandler: function(form){
                form.submit();
            }
        });
    });
{/literal}
</script>