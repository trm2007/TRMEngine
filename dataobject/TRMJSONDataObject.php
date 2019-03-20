<?php

namespace TRMEngine\DataObject;

use TRMEngine\DataObject\Interfaces\TRMDataObjectInterface;

/**
 * ����� ��� �������������� ������� ������ �� ������� ���� TRMDataObject � JSON-������
 *
 * @author TRM 2018.07.22
 */
class TRMJSONDataObject
{
/**
 * ����������� ������ ������ ������� TRMDataObject � JSON-������
 * 
 * @param TRMDataObject $do
 * @return string - JSON
 */
public function __invoke(TRMDataObjectInterface $do)
{
    return json_encode($do->getDataArray());
}


} // TRMJSONDataObject
