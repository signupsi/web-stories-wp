/*
 * Copyright 2020 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     https://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * External dependencies
 */
import styled from 'styled-components';
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { useContext, useCallback } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import useInspector from '../../inspector/useInspector';
import panelContext from './context';
import DragHandle from './handle';
import Arrow from './arrow.svg';

const Header = styled.h2`
  background-color: ${({ theme, isPrimary }) =>
    isPrimary ? theme.colors.fg.v6 : theme.colors.fg.v1};
  border: 0 solid ${({ theme }) => theme.colors.fg.v6};
  border-top-width: ${({ isPrimary }) => (isPrimary ? 0 : '1px')};
  color: ${({ theme }) => theme.colors.bg.v2};
  ${({ hasResizeHandle }) => hasResizeHandle && 'padding-top: 0;'}
  margin: 0;
  position: relative;
  display: flex;
  flex-direction: column;
  justify-content: flex-start;
  align-items: stretch;
`;

const HeaderButton = styled.button.attrs({ type: 'button' })`
  color: inherit;
  border: 0;
  padding: 10px 20px;
  background: transparent;
  display: flex;
  justify-content: space-between;
  align-items: center;
  cursor: pointer;
`;

const Heading = styled.span`
  color: inherit;
  margin: 0;
  font-weight: 500;
  font-size: 16px;
  line-height: 19px;
`;

const Collapse = styled.span`
  color: inherit;
  width: 28px;
  height: 28px;
  display: flex; /* removes implicit line-height padding from child element */
  opacity: 0.54;

  svg {
    width: 28px;
    height: 28px;
    ${({ isCollapsed }) => isCollapsed && `transform: rotate(.5turn);`}
  }
`;

function Title({ children, isPrimary, isResizable }) {
  const {
    state: { isCollapsed, height, panelContentId },
    actions: { collapse, expand, setHeight },
  } = useContext(panelContext);
  const {
    state: { inspectorContentHeight },
  } = useInspector();

  // Max panel height is set to 70% of full available height.
  const maxHeight = Math.round(inspectorContentHeight * 0.7);

  const handleHeightChange = useCallback(
    (deltaHeight) =>
      setHeight((value) =>
        Math.max(0, Math.min(maxHeight, value + deltaHeight))
      ),
    [setHeight, maxHeight]
  );

  const titleLabel = isCollapsed
    ? __('Expand panel', 'web-stories')
    : __('Collapse panel', 'web-stories');

  return (
    <Header isPrimary={isPrimary} hasResizeHandle={isResizable && !isCollapsed}>
      {isResizable && !isCollapsed && (
        <DragHandle
          height={height}
          minHeight={0}
          maxHeight={maxHeight}
          handleHeightChange={handleHeightChange}
        />
      )}
      <HeaderButton
        onClick={isCollapsed ? expand : collapse}
        aria-label={titleLabel}
        aria-expanded={!isCollapsed}
        aria-controls={panelContentId}
      >
        <Heading>{children}</Heading>
        <Collapse isCollapsed={isCollapsed}>
          <Arrow />
        </Collapse>
      </HeaderButton>
    </Header>
  );
}

Title.propTypes = {
  children: PropTypes.oneOfType([
    PropTypes.arrayOf(PropTypes.node),
    PropTypes.node,
  ]).isRequired,
  isPrimary: PropTypes.bool,
  isResizable: PropTypes.bool,
};

Title.defaultProps = {
  isPrimary: false,
  isResizable: false,
};

export default Title;
