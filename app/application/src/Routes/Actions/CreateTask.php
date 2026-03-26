<?php

	namespace Routes\Actions;

	class CreateTask extends AbstractAction {

		public function run( array $parameters = [] ) {
			try {
				$result = $this->runApi('\Routes\API\ogc\Processes\CalculationTask', $parameters);

				return $this->getRenderable( null, array_merge( $parameters, [
						'redirectLink' => 'Pages/WaitForTask',
						'taskName' => $result['jobId']
					]));
			} catch ( \Throwable $e ) {
				return $this->getRenderable( 'CreateTaskError' , array_merge( $parameters, [
						'redirectLink' => 'Pages/CreateTaskForm',
						'message' => $e->getMessage()
					]));
			}
		}

	}
?>
