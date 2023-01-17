import React, {FC, useCallback, useState} from 'react';
import styled from 'styled-components';
import {SectionTitle, SwitcherButton, Table} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {TargetPlaceholder} from './components/TargetPlaceholder';
import {ProductMapping as ProductMappingType} from './models/ProductMapping';
import {ProductMappingSchema} from './models/ProductMappingSchema';
import {TargetSourceAssociation} from './components/TargetSourceAssociation';
import {ProductMappingErrors} from './models/ProductMappingErrors';
import {SourcePanel} from './components/SourcePanel';
import {Source} from './models/Source';
import {Target} from './models/Target';

const MappingContainer = styled.div`
    display: flex;
    gap: 40px;
    padding-top: 10px;
`;
const TargetContainer = styled.div`
    flex-basis: 50%;
    flex-grow: 1;
`;
const SourceContainer = styled.div`
    flex-basis: 50%;
`;

const createTargetsFromProductMapping = (mapping: ProductMappingType): [string, Source][] => {
    const targets = Object.entries(mapping);

    // move UUID to the top
    const index = targets.findIndex(([target]) => target === 'uuid');
    const uuid = targets.splice(index, 1)[0];
    targets.unshift(uuid);

    return targets;
};

const hasError = (errors: object | undefined): boolean => {
    if (errors === undefined) {
        return false;
    }

    return (
        Object.entries(errors).filter(([, value]) => {
            if (typeof value === 'object' && hasError(value)) {
                return true;
            }
            return typeof value === 'string';
        }).length > 0
    );
};

type Props = {
    productMapping: ProductMappingType;
    productMappingSchema: ProductMappingSchema | undefined;
    errors: ProductMappingErrors;
    onChange: (values: ProductMappingType) => void;
};

export const ProductMapping: FC<Props> = ({productMapping, productMappingSchema, errors, onChange}) => {
    const translate = useTranslate();

    const [selectedTarget, setSelectedTarget] = useState<Target | null>(null);
    const [selectedSource, setSelectedSource] = useState<Source | null>(null);

    const handleClick = useCallback(
        (targetCode, source) => {
            if (productMappingSchema === undefined) {
                return;
            }
            const target: Target = {
                code: targetCode,
                label: productMappingSchema.properties[targetCode]?.title ?? targetCode,
                type: productMappingSchema.properties[targetCode].type,
                format: productMappingSchema.properties[targetCode].format ?? null,
            };
            if (undefined !== productMappingSchema.properties[targetCode].description) {
                target.description = productMappingSchema.properties[targetCode].description;
            }
            setSelectedTarget(target);
            setSelectedSource(source);
        },
        [productMappingSchema]
    );

    const handleSourceUpdate = useCallback(
        (source: Source) => {
            if (selectedTarget !== null) {
                onChange({
                    ...productMapping,
                    [selectedTarget.code]: source,
                });
                setSelectedSource(source);
            }
        },
        [selectedTarget, onChange, productMapping]
    );

    const targets = createTargetsFromProductMapping(productMapping);

    return (
        <MappingContainer data-testid={'product-mapping'}>
            <TargetContainer>
                <SectionTitle>
                    <SectionTitle.Title>{translate('akeneo_catalogs.product_mapping.target.title')}</SectionTitle.Title>
                    <SectionTitle.Spacer />
                    <SwitcherButton label={translate('akeneo_catalogs.product_mapping.target.filter.label')}>
                        {translate('akeneo_catalogs.product_mapping.target.filter.option.all')}
                    </SwitcherButton>
                </SectionTitle>
                <Table>
                    <Table.Header>
                        <Table.HeaderCell>
                            {translate('akeneo_catalogs.product_mapping.target.table.target')}
                        </Table.HeaderCell>
                        <Table.HeaderCell>
                            {translate('akeneo_catalogs.product_mapping.target.table.source')}
                        </Table.HeaderCell>
                    </Table.Header>
                    <Table.Body>
                        {(targets.length === 0 || undefined === productMappingSchema) && <TargetPlaceholder />}
                        {targets.length > 0 && undefined !== productMappingSchema && (
                            <>
                                {targets.map(([targetCode, source]) => {
                                    return (
                                        <TargetSourceAssociation
                                            isSelected={selectedTarget?.code === targetCode}
                                            key={targetCode}
                                            onClick={handleClick}
                                            targetCode={targetCode}
                                            targetLabel={productMappingSchema.properties[targetCode]?.title}
                                            source={source}
                                            hasError={hasError(errors[targetCode])}
                                        />
                                    );
                                })}
                            </>
                        )}
                    </Table.Body>
                </Table>
            </TargetContainer>
            <SourceContainer>
                <SourcePanel
                    target={selectedTarget}
                    source={selectedSource}
                    onChange={handleSourceUpdate}
                    errors={selectedTarget === null ? null : errors[selectedTarget?.code]}
                />
            </SourceContainer>
        </MappingContainer>
    );
};
