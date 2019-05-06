class AuditoriasPresentador extends CatalogoPresentador
{
	 constructor(vista)
	 {
		 super(vista, new AuditoriasRepositorio());
	 }
	 
}