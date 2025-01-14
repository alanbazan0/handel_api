class TiposSocioComercialPresentador extends CatalogoPresentador
{
    constructor(vista)
    {
        super(vista,new TiposSocioComercialRepositorio());
    }
}
