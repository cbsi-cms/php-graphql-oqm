<?php

namespace GraphQL\SchemaGenerator\CodeGenerator;

use GraphQL\Enumeration\FieldTypeKindEnum;
use GraphQL\SchemaGenerator\CodeGenerator\CodeFile\ClassFile;
use GraphQL\Util\StringLiteralFormatter;

/**
 * Class InterfaceObjectClassBuilder
 *
 * @package GraphQL\SchemaManager\CodeGenerator
 */
class InterfaceObjectClassBuilder extends ObjectClassBuilder
{
    /**
     * InterfaceObjectClassBuilder constructor.
     *
     * @param string $writeDir
     * @param string $objectName
     * @param string $namespace
     */
    public function __construct(string $writeDir, string $objectName, string $namespace = self::DEFAULT_NAMESPACE)
    {
        $className = $objectName . 'QueryObject';

        $this->classFile = new ClassFile($writeDir, $className);
        $this->classFile->setNamespace($namespace);
        if ($namespace !== self::DEFAULT_NAMESPACE) {
            $this->classFile->addImport('GraphQL\\SchemaObject\\UnionObject');
        }
        $this->classFile->extendsClass('UnionObject');

        $this->classFile->addConstant('OBJECT_NAME', $objectName);
    }

    /**
     * @param string $fieldName
     */
    public function addScalarField(string $fieldName, bool $isDeprecated, ?string $deprecationReason)
    {
        $upperCamelCaseProp = StringLiteralFormatter::formatUpperCamelCase($fieldName);
        $this->addSimpleSelector($fieldName, $upperCamelCaseProp, $isDeprecated, $deprecationReason);
    }

    /**
     * @param string $fieldName
     * @param string $typeName
     * @param string $typeKind
     * @param string $argsObjectName
     * @param bool $isDeprecated
     * @param string|null $deprecationReason
     */
    public function addObjectField(string $fieldName, string $typeName, string $typeKind, string $argsObjectName, bool $isDeprecated, ?string $deprecationReason)
    {
        $upperCamelCaseProp = StringLiteralFormatter::formatUpperCamelCase($fieldName);
        $this->addObjectSelector($fieldName, $upperCamelCaseProp, $typeName, $typeKind, $argsObjectName, $isDeprecated, $deprecationReason);
    }

    /**
     * @param string $propertyName
     * @param string $upperCamelName
     * @param bool $isDeprecated
     * @param string|null $deprecationReason
     */
    protected function addSimpleSelector(string $propertyName, string $upperCamelName, bool $isDeprecated, ?string $deprecationReason)
    {
        $method = "public function select$upperCamelName()
{
    \$this->selectField(\"$propertyName\");

    return \$this;
}";
        $this->classFile->addMethod($method, $isDeprecated, $deprecationReason);
    }

    /**
     * @param string $fieldName
     * @param string $upperCamelName
     * @param string $fieldTypeName
     * @param string $fieldTypeKind
     * @param string $argsObjectName
     * @param bool $isDeprecated
     * @param string|null $deprecationReason
     */
    protected function addObjectSelector(string $fieldName, string $upperCamelName, string $fieldTypeName, string $fieldTypeKind, string $argsObjectName, bool $isDeprecated, ?string $deprecationReason)
    {
        $objectClass = $fieldTypeName . ($fieldTypeKind === FieldTypeKindEnum::UNION_OBJECT ? 'UnionObject' : 'QueryObject');
        $method = "public function select$upperCamelName($argsObjectName \$argsObject = null)
{
    \$object = new $objectClass(\"$fieldName\");
    if (\$argsObject !== null) {
        \$object->appendArguments(\$argsObject->toArray());
    }
    \$this->selectField(\$object);

    return \$object;
}";
        $this->classFile->addMethod($method, $isDeprecated, $deprecationReason);
    }


    /**
     * @param string $typeName
     */
    public function addPossibleType(string $typeName)
    {
        $upperCamelCaseTypeName = StringLiteralFormatter::formatUpperCamelCase($typeName);
        $objectClassName = $typeName . 'QueryObject';
        $method = "public function on$upperCamelCaseTypeName()
{
    \$object = new $objectClassName();
    \$this->addPossibleType(\$object);

    return \$object;
}";
        $this->classFile->addMethod($method);
    }

    /**
     * This method builds the class and writes it to the file system
     */
    public function build(): void
    {
        $this->classFile->writeFile();
    }
}