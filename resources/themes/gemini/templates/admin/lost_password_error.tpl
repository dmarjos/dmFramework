<div style="width: 350px; position: relative; margin-left: auto; margin-right: auto; margin-top: 60px;">
   <div class="block-login-content">
        <h1>Recuperar contrase&ntilde;a</h1>
        <h2><font color="red">Usuario o e-mail desconocido</font></h2>
        <form id="signinForm" method="post" action="{Application::getLink(Application::get("SELF"))}">
		<input type="hidden" name="step" value="1" />
            
        <div class="form-group">                        
            <input type="text" name="user" class="form-control" placeholder="Nombre de usuario o direccion de email" value=""/>
        </div>

        <input type="submit" class="btn btn-primary btn-block" value="Recuperar contrase&ntilde;a"/>                                        
        
        </form>
        <div class="sp"></div>
        <div class="pull-left">
            © All Rights Reserved Divalia Mexico 2014
        </div>
    </div>
</div>
