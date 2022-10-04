import React, {useCallback, useMemo, useState} from 'react';
import {Breadcrumb, Button, Helper} from 'akeneo-design-system';
import {PageHeader, PimView, useTranslate} from '@akeneo-pim-community/shared';
import {QueryClient, QueryClientProvider} from 'react-query';
import {CreateGeneratorModal} from './components/CreateGeneratorModal';
import {IdentifierGenerator} from '../models';
import {EditGenerator} from './components/EditGenerator';

const queryClient = new QueryClient();

enum Screens {
  LIST,
  CREATE,
  EDIT,
}

const IdentifierGeneratorApp: React.FC = () => {
  const translate = useTranslate();
  const [currentScreen, setCurrentScreen] = useState(Screens.LIST);
  const [generators, setGenerators] = useState<IdentifierGenerator[]>([]);
  const [generator, setGenerator] = useState<IdentifierGenerator | null>(null);

  const goToCreation = useCallback(() => setCurrentScreen(Screens.CREATE), []);
  const goToList = useCallback(() => setCurrentScreen(Screens.LIST), []);

  const create = useCallback((value: IdentifierGenerator) => {
    setGenerator(value);
    setCurrentScreen(Screens.EDIT);
  }, []);

  const creationIsDisabled = useMemo(() => generators.length > 0, [generators.length]);

  return (
    <QueryClientProvider client={queryClient}>
      <div>
        <Helper level="error">
          Under Construction: The Akeneo Product Team is hard at work developing new features for you. This feature will
          launch soon, but is currently under development. Please do not attempt to use this feature as it could lead to
          unexpected behaviors that impact your product data.
        </Helper>
        <PageHeader>
          <PageHeader.Breadcrumb>
            <Breadcrumb>
              <Breadcrumb.Step href="#">{translate('pim_title.pim_settings_index')}</Breadcrumb.Step>
              <Breadcrumb.Step href="#">{translate('pim_title.akeneo_identifier_generator_index')}</Breadcrumb.Step>
            </Breadcrumb>
          </PageHeader.Breadcrumb>
          <PageHeader.UserActions>
            <PimView
              className="AknTitleContainer-userMenuContainer AknTitleContainer-userMenu"
              viewName="pim-identifier-generator-user-navigation"
            />
          </PageHeader.UserActions>
          <PageHeader.Actions>
            <Button onClick={goToCreation} disabled={creationIsDisabled}>
              {translate('pim_common.create')}
            </Button>
          </PageHeader.Actions>
          <PageHeader.Title>{translate('pim_title.akeneo_identifier_generator_index')}</PageHeader.Title>
        </PageHeader>
        <div>
          {currentScreen === Screens.CREATE && <CreateGeneratorModal onClose={goToList} onSave={create} />}
          {currentScreen === Screens.EDIT && <EditGenerator generator={generator} />}
        </div>
      </div>
    </QueryClientProvider>
  );
};

export {IdentifierGeneratorApp};
