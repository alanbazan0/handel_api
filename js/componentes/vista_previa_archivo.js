class VistaPreviaArchivo
{
	
	visualizar(vista, carpeta, id, archivo)
	{
		var _this = this;
		vista.mostrarFormularioHTML(HANDEL_API+"/html/modales/ver_archivo.php",this, null, function()
		{
			
			_this.vistaPreviaArchivo(vista,carpeta,id,archivo);
			
		},null,"archivoModal","","", function()
		{
			
		},function()
		{
			
		});
	}
	
	vistaPreviaArchivo(vista,carpeta,id, archivo)
	{
		 $("#pdf").hide();
		$("#officeDiv").hide();
		$("#contenedorEvidenciaImage").hide();
		var _this = this;
		$("#descargarButton").click(function()
			{
			//var archivo = "evidencia"+_this.modeloEdicion.id+"_" +encodeURIComponent(_this.modeloEdicion.nombreArchivo);
			//archivo = encodeURIComponent(archivo);
			var url = HANDEL_API + "/" +carpeta +"/"+ id+"/"+archivo;
			var submitForm = vista.getNewSubmitForm(url);
		    submitForm.target= "_blank";
		    submitForm.submit();
		});
		if(archivo=="")
			$('#evidenciaImage').attr("src",HANDEL_API + "/images/tipos_archivo/vacio.png");
		else
		{
			try
			{
				var elementos = archivo.split(".");
				if(elementos.length>1)
				{
					var tipo= elementos[elementos.length-1];
					var archivo = encodeURIComponent(archivo);
					var url = HANDEL_API + "/"+carpeta+"/" + id+"/"+archivo;
					switch(tipo)
					{
						case "doc":
						case "docx":
							$("#officeDiv").show();
							url = this.getUrlOfficeOnline(url);  
							$("#officeIframe").attr("src",url);
						break;
						case "xls":
						case "xlsx":
							$("#officeDiv").show();
							url = this.getUrlOfficeOnline(url);  
							$("#officeIframe").attr("src",url);
						break;
						case "ppt":
						case "pptx":
						case "ppsx":
							$("#officeDiv").show();
							url = this.getUrlOfficeOnline(url);  
							$("#officeIframe").attr("src",url);
						break;
						case "pdf":
							$("#officeDiv").show();
							//$('#evidenciaImage').hide(); 
							 //$('#evidenciaImage').attr('src',HANDEL_API + "/images/tipos_archivo/pdf.png");
							 
							 
							 
							
							//var url = HANDEL_API + "/php/archivos_evidencias/" + archivo;
							//this.showPDF(url);
							$("#officeIframe").attr("src",url);
							
							 
						break;
	//					case "txt":
	//						 $('#evidenciaImage').attr('src',HANDEL_API + "/images/tipos_archivo/txt.png");
	//					break;
						default:
							$("#evidenciaImage").css({'width': '50%'});
							$('#evidenciaImage').attr('src',HANDEL_API + "/images/tipos_archivo/archivo.png");
						break;
						case "jpg":
						case "png":
						case "bmp":
							/*$("#evidenciaImage").css({'width': '100%'});
							if(archivoSeleccionado.subido)
							{
								var archivo = encodeURIComponent(archivoSeleccionado.nombre);
								var url = HANDEL_API + "/php/archivos_avances/avance" + this._avanceSeleccionado.id+"/"+ archivo;
								$('#evidenciaImage').attr('src',url);
							}
							else
							{
								$('#evidenciaImage').attr('src',archivoSeleccionado.result);
							}*/
							$('#contenedorEvidenciaImage').show();
							$('#evidenciaImage').width("100%");
							$('#evidenciaImage').attr('src',url);
						break;
						
						
					}
				}
				else
				{
					$('#evidenciaImage').attr('src',HANDEL_API + "/images/tipos_archivo/archivo.png");
				}
			}
			catch(e)
			{
				 $('#evidenciaImage').attr('src',HANDEL_API + "/images/tipos_archivo/archivo.png");
			}
			
			
		}
		
	}
	
	getUrlOfficeOnline(url)
	{
		var urlOffice =  "https://view.officeapps.live.com/op/embed.aspx?src="+url; 
		return urlOffice;				
	}
	
	
	
}