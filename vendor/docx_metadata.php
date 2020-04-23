<?php
	class docxmetadata{
		var $metadocument = "";
		var $mxsi = " xsi:type=\"dcterms:W3CDTF\"";

		function setDocument($path){
			$zip = new ZipArchive;
			$res = $zip->open($path);
			if ($res === TRUE) {
				$folder = md5(time());
				mkdir($folder, 0700);
				$zip->extractTo($folder, array("docProps/core.xml"));
				$zip->close();
				$this->metadocument = file_get_contents($folder."/docProps/core.xml");	
				unlink($folder."/docProps/core.xml");
				rmdir($folder."/docProps");
				rmdir($folder);
			}	
		}
		
		function getMeta($x, $dc="dc", $xsi=''){
		   /* print_r($this->metadocument);
		    die();*/
		    if (strpos($this->metadocument, $x) !== false) {
    			$r = "";
    			$s = explode("</$dc:$x>", $this->metadocument);
    			$e = explode("<$dc:$x$xsi>", $s[0]);
    			$r = isset($e[1]) ? $e[1] : $e[0] ;
    			return $r;
		    }
		    
		    return '';
		}
		
		function getDateCreated(){
			return $this->getMeta("created", 'dcterms', $this->mxsi);
		}

		function getDateModified(){
			return $this->getMeta("modified", 'dcterms', $this->mxsi);
		}
		function getTitle(){
			return $this->getMeta("title");
		}

		function getSubject(){
			return $this->getMeta("subject");
		}

		function getCreator(){
			return $this->getMeta("creator");
		}

		function getKeywords(){
			return $this->getMeta("keywords", 'cp');
		}

		function getDescription(){
			return $this->getMeta("description");
		}

		function getLastModifiedBy(){
			return $this->getMeta("lastModifiedBy", 'cp');
		}

		function getRevision(){
			return $this->getMeta("revision", 'cp');
		}
		
		function getCategory(){
		    return $this->getMeta("category", 'cp');
		}
		
		function alldata()
		{
		    $data['title'] = $this->getTitle();
		    $data['Subject'] = $this->getSubject();
		    $data['Creator'] = $this->getCreator();
		    $data['Keywords'] = $this->getKeywords();
		    $data['Description'] = $this->getDescription();
		    $data['LastModifiedBy'] = $this->getLastModifiedBy();
		    $data['Revision'] = $this->getRevision();
		    $data['DateCreated'] = $this->getDateCreated();
		    $data['DateModified'] = $this->getDateModified();
		    $data['Category'] = $this->getCategory();
		    return $data;
		}
	}
?>