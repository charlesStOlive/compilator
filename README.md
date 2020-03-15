# compilator
Attetion : 
Modifier dans la fonction : 
protected function setValueForPart($search, $replace, $documentPartXML, $limit)
    {


        Ajouter : 
        $replace = preg_replace('~\R~u', '<w:t><w:br/></w:t>', $replace);