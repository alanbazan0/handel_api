class ServiciosPresentador extends CatalogoPresentador
{
    constructor(vista)
    {
        super(vista,new ServiciosRepositorio());
    }
}
