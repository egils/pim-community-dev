import React, {useCallback, useState} from 'react';
import {AttributesIllustration, Button, Field, Modal, TextInput} from 'akeneo-design-system';
import {useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {Styled} from '../components/Styled';
import {useIdentifierAttributes} from '../hooks';
import {useGeneratorEditContext} from '../context/useGeneratorEditContext';

type CreateGeneratorModalProps = {
  onClose: () => void;
  onSave: () => void;
};

const CreateGeneratorModal: React.FC<CreateGeneratorModalProps> = ({onClose, onSave}) => {
  const [label, setLabel] = useState<string>('');
  const [code, setCode] = useState<string>('');
  const [isCodeDirty, setIsCodeDirty] = useState(false);
  const [target, setTarget] = useState<string | undefined>();
  const {data} = useIdentifierAttributes();

  React.useEffect(() => {
    if (data) {
      setTarget(data[0].code);
    }
  }, [data]);

  const translate = useTranslate();
  const userContext = useUserContext();
  const uiLocale = userContext.get('uiLocale');
  const {generator, setGenerator} = useGeneratorEditContext();

  const onLabelChange = useCallback(
    (value: string) => {
      setLabel(value);
      if (!isCodeDirty) setCode(value.replace(/[^a-zA-Z0-9]/g, '_'));
    },
    [isCodeDirty]
  );

  const onCodeChange = useCallback(value => {
    setIsCodeDirty(true);
    setCode(value);
  }, []);

  const onConfirm = useCallback(() => {
    if (target) {
      setGenerator({
        ...generator,
        code,
        target,
        labels: {[uiLocale]: label}
      });
      onSave();
    }
  }, [target, setGenerator, generator, code, uiLocale, label, onSave]);

  const isFormInvalid = React.useMemo(() => code === '', [code]);

  return (
    <Modal closeTitle={translate('pim_common.close')} illustration={<AttributesIllustration />} onClose={onClose}>
      <Modal.SectionTitle color="brand">{translate('pim_title.akeneo_identifier_generator_index')}</Modal.SectionTitle>
      <Modal.Title>{translate('pim_identifier_generator.create.form.title')}</Modal.Title>
      <Styled.FormContainer>
        <Field label={translate('pim_common.label')} locale={uiLocale}>
          <TextInput name="label" value={label} onChange={onLabelChange} />
        </Field>
        <Field label={translate('pim_common.code')} requiredLabel={translate('pim_common.required_label')}>
          <TextInput name="code" value={code} onChange={onCodeChange} />
        </Field>
      </Styled.FormContainer>
      <Modal.BottomButtons>
        <Button onClick={onClose} level="tertiary">
          {translate('pim_common.cancel')}
        </Button>
        <Button onClick={onConfirm} disabled={isFormInvalid}>
          {translate('pim_common.confirm')}
        </Button>
      </Modal.BottomButtons>
    </Modal>
  );
};

export {CreateGeneratorModal};
