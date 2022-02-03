import os
import time
import statistics


def measureDockerRun(imageTag):
    start = time.time()
    os.system("docker run  --rm " +
              "-v $(pwd):/data/project " +
              "-v $(pwd)/out:/data/results " +
              "-e _JAVA_OPTIONS=-Xmx512m " +
              imageTag
              )
    end = time.time()
    return end - start


def measureGc(gcImageNumber):
    imageTag = "repo.labs.intellij.net/static-analyser/qodana:" + gcImageNumber
    os.system("docker pull " + imageTag)
    times = []

    for _ in range(25):
        times.append(measureDockerRun(imageTag))
    return times


def printResults(resultsName, results):
    print(resultsName, statistics.mean(results), statistics.stdev(results), results)

parallelResults = measureGc("20978.2565")
g1Results = measureGc("20978.2567")
zResults = measureGc("20920.2561")
g1JBR11Results = measureGc("20920.2562")

printResults("Parallel", parallelResults)
printResults("G1 JBR 17", g1Results)
printResults("Z", zResults)
printResults("G1 JBR 11", g1JBR11Results)
